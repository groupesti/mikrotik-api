<?php

declare(strict_types=1);

namespace MikroTik\Transport\Api;

use MikroTik\DTO\ConnectionConfig;
use MikroTik\DTO\RequestOptions;
use MikroTik\Exceptions\ApiException;

final class ApiClient
{
    private $stream = null;
    private bool $loggedIn = false;

    public function __construct(
        private readonly ConnectionConfig $cfg,
        private readonly RequestOptions $opts
    ) {
        $this->connect();
        $this->login();
    }

    public function close(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;
    }

    /** @param list<string> $words
        @param array<string,mixed> $params
        @return array{data: array<int,array<string,string>>|array<string,mixed>, meta: array<string,mixed>} */
    public function command(array $words, array $params = []): array
    {
        if (!is_resource($this->stream)) {
            throw new ApiException('API socket not connected');
        }

        $sentence = $words;
        foreach ($params as $k => $v) {
            $key = (string)$k;
            if ($key === '') { continue; }
            // RouterOS API expects =key=value
            $sentence[] = '=' . $key . '=' . $this->toScalarString($v);
        }

        $this->writeSentence($sentence);

        $rows = [];
        $meta = ['words' => $sentence];

        while (true) {
            $reply = $this->readSentence();
            if ($reply === []) {
                throw new ApiException('Empty reply');
            }

            $type = $reply[0] ?? '';
            if ($type === '!trap') {
                $detail = $this->kvFromSentence($reply);
                $msg = (string)($detail['message'] ?? $detail['category'] ?? 'trap');
                throw new ApiException('RouterOS API !trap: ' . $msg);
            }

            if ($type === '!done') {
                $done = $this->kvFromSentence($reply);
                if ($done !== []) {
                    $meta['done'] = $done;
                }
                break;
            }

            if ($type === '!re') {
                $rows[] = $this->kvFromSentence($reply);
                continue;
            }

            // sometimes we may get other replies; collect
            $rows[] = $this->kvFromSentence($reply);
        }

        return [
            'data' => $rows,
            'meta' => $meta,
        ];
    }

    private function connect(): void
    {
        $port = $this->cfg->apiPort();
        $target = $this->cfg->host . ':' . $port;

        $contextOptions = [];
        if ($this->cfg->tls) {
            // RouterOS uses TLS for api-ssl; verify config is not always possible with self-signed
            $contextOptions['ssl'] = [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ];
        }

        $context = stream_context_create($contextOptions);
        $scheme = $this->cfg->tls ? 'tls' : 'tcp';
        $stream = @stream_socket_client(
            $scheme . '://' . $target,
            $errno,
            $errstr,
            (float)$this->opts->timeoutSeconds,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!is_resource($stream)) {
            throw new ApiException('API connect failed: ' . $errstr . ' (' . $errno . ')');
        }

        stream_set_timeout($stream, (int)ceil($this->opts->timeoutSeconds));
        stream_set_blocking($stream, true);
        $this->stream = $stream;
    }

    private function login(): void
    {
        // Try "direct" login first (works on many ROS versions)
        $this->writeSentence(['/login', '=name=' . $this->cfg->username, '=password=' . $this->cfg->password]);
        $first = $this->readSentence();
        if ($first === []) {
            throw new ApiException('Login failed: empty');
        }

        if (($first[0] ?? '') === '!done') {
            $this->loggedIn = true;
            return;
        }

        // Some routers return !trap then we fail
        if (($first[0] ?? '') === '!trap') {
            $detail = $this->kvFromSentence($first);
            $msg = (string)($detail['message'] ?? 'trap');
            throw new ApiException('Login !trap: ' . $msg);
        }

        // Legacy challenge flow: expect !done with =ret=
        if (($first[0] ?? '') === '!done') {
            $kv = $this->kvFromSentence($first);
            if (isset($kv['ret'])) {
                $ret = (string)$kv['ret'];
                $resp = $this->legacyResponse($ret);
                $this->writeSentence(['/login', '=name=' . $this->cfg->username, '=response=' . $resp]);
                $done = $this->readSentence();
                if (($done[0] ?? '') !== '!done') {
                    throw new ApiException('Legacy login failed');
                }
                $this->loggedIn = true;
                return;
            }
        }

        // If we received !re etc, keep reading until !done and see if ret exists
        $ret = null;
        if (($first[0] ?? '') === '!re') {
            $kv = $this->kvFromSentence($first);
            if (isset($kv['ret'])) { $ret = (string)$kv['ret']; }
            while (true) {
                $s = $this->readSentence();
                if (($s[0] ?? '') === '!done') { break; }
                if (($s[0] ?? '') === '!re') {
                    $kv2 = $this->kvFromSentence($s);
                    if (isset($kv2['ret'])) { $ret = (string)$kv2['ret']; }
                }
                if (($s[0] ?? '') === '!trap') {
                    $d = $this->kvFromSentence($s);
                    throw new ApiException('Login !trap: ' . (string)($d['message'] ?? 'trap'));
                }
            }
            if ($ret !== null) {
                $resp = $this->legacyResponse($ret);
                $this->writeSentence(['/login', '=name=' . $this->cfg->username, '=response=' . $resp]);
                $done = $this->readSentence();
                if (($done[0] ?? '') !== '!done') {
                    throw new ApiException('Legacy login failed');
                }
                $this->loggedIn = true;
                return;
            }
        }

        // Otherwise: assume logged-in if we didn't trap and got done later
        // But safest: if not done, fail
        throw new ApiException('Login failed (unexpected reply type: ' . (string)($first[0] ?? '') . ')');
    }

    private function legacyResponse(string $retHex): string
    {
        // response = 00 + md5(0x00 + password + challengeBytes)
        $chal = @hex2bin($retHex);
        if ($chal === false) {
            throw new ApiException('Invalid legacy challenge hex');
        }
        $data = chr(0) . $this->cfg->password . $chal;
        $md5 = md5($data, true);
        return '00' . bin2hex($md5);
    }

    /** @param list<string> $words */
    private function writeSentence(array $words): void
    {
        foreach ($words as $w) {
            $this->writeWord($w);
        }
        $this->writeWord(''); // sentence terminator
    }

    private function writeWord(string $word): void
    {
        $len = strlen($word);
        $this->writeLength($len);
        if ($len > 0) {
            $this->writeRaw($word);
        }
    }

    private function writeLength(int $len): void
    {
        // variable length encoding per RouterOS API spec
        if ($len < 0x80) {
            $this->writeRaw(chr($len));
            return;
        }
        if ($len < 0x4000) {
            $len |= 0x8000;
            $this->writeRaw(chr(($len >> 8) & 0xFF) . chr($len & 0xFF));
            return;
        }
        if ($len < 0x200000) {
            $len |= 0xC00000;
            $this->writeRaw(chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF));
            return;
        }
        if ($len < 0x10000000) {
            $len |= 0xE0000000;
            $this->writeRaw(chr(($len >> 24) & 0xFF) . chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF));
            return;
        }

        $this->writeRaw(chr(0xF0) . chr(($len >> 24) & 0xFF) . chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF));
    }

    /** @return list<string> */
    private function readSentence(): array
    {
        $words = [];
        while (true) {
            $w = $this->readWord();
            if ($w === '') {
                break;
            }
            $words[] = $w;
        }
        return $words;
    }

    private function readWord(): string
    {
        $len = $this->readLength();
        if ($len === 0) {
            return '';
        }
        return $this->readRaw($len);
    }

    private function readLength(): int
    {
        $c = ord($this->readRaw(1));
        if (($c & 0x80) === 0x00) {
            return $c;
        }
        if (($c & 0xC0) === 0x80) {
            $c2 = ord($this->readRaw(1));
            return (($c & 0x3F) << 8) + $c2;
        }
        if (($c & 0xE0) === 0xC0) {
            $c2 = ord($this->readRaw(1));
            $c3 = ord($this->readRaw(1));
            return (($c & 0x1F) << 16) + ($c2 << 8) + $c3;
        }
        if (($c & 0xF0) === 0xE0) {
            $c2 = ord($this->readRaw(1));
            $c3 = ord($this->readRaw(1));
            $c4 = ord($this->readRaw(1));
            return (($c & 0x0F) << 24) + ($c2 << 16) + ($c3 << 8) + $c4;
        }
        if ($c === 0xF0) {
            $c2 = ord($this->readRaw(1));
            $c3 = ord($this->readRaw(1));
            $c4 = ord($this->readRaw(1));
            $c5 = ord($this->readRaw(1));
            return ($c2 << 24) + ($c3 << 16) + ($c4 << 8) + $c5;
        }

        throw new ApiException('Invalid length encoding');
    }

    private function writeRaw(string $data): void
    {
        $left = strlen($data);
        $off = 0;
        while ($left > 0) {
            $w = fwrite($this->stream, substr($data, $off));
            if ($w === false || $w === 0) {
                throw new ApiException('Socket write failed');
            }
            $off += $w;
            $left -= $w;
        }
    }

    private function readRaw(int $len): string
    {
        $buf = '';
        while (strlen($buf) < $len) {
            $chunk = fread($this->stream, $len - strlen($buf));
            if ($chunk === false || $chunk === '') {
                $meta = stream_get_meta_data($this->stream);
                if (isset($meta['timed_out']) && $meta['timed_out'] === true) {
                    throw new ApiException('Socket read timeout');
                }
                throw new ApiException('Socket read failed');
            }
            $buf .= $chunk;
        }
        return $buf;
    }

    /** @param list<string> $sentence
        @return array<string,string> */
    private function kvFromSentence(array $sentence): array
    {
        $out = [];
        foreach ($sentence as $w) {
            if (str_starts_with($w, '=')) {
                $eq = substr($w, 1);
                $pos = strpos($eq, '=');
                if ($pos === false) { continue; }
                $k = substr($eq, 0, $pos);
                $v = substr($eq, $pos + 1);
                $out[$k] = $v;
            }
        }
        return $out;
    }

    private function toScalarString(mixed $v): string
    {
        if (is_bool($v)) { return $v ? 'yes' : 'no'; }
        if (is_int($v) || is_float($v)) { return (string)$v; }
        if (is_string($v)) { return $v; }
        if ($v === null) { return ''; }
        // fallback JSON for complex
        $json = json_encode($v, JSON_UNESCAPED_SLASHES);
        return $json === false ? '' : $json;
    }
}
