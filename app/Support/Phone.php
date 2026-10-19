<?php

namespace App\Support;

class Phone
{
    /**
     * Nomor seluler Indonesia dengan pemisah umum (spasi, strip, kurung) yang masih boleh diketik.
     * Awalan 0/62 opsional karena Excel membuang angka 0 di depan sel numerik.
     */
    public const MOBILE_PATTERN = '/^(\+?62[\s\-]?|0)?8[\d\s\-().]{7,18}$/';

    /**
     * @return array<int, string>
     */
    public static function rules(bool $required = true, int $max = 30): array
    {
        return [$required ? 'required' : 'nullable', 'string', 'max:'.$max, 'regex:'.self::MOBILE_PATTERN];
    }

    /**
     * Bentuk 62812xxxx yang dipakai kolom nomor wali/guru dan gateway WhatsApp.
     */
    public static function international(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        return match (true) {
            str_starts_with($digits, '0') => '62'.substr($digits, 1),
            str_starts_with($digits, '8') => '62'.$digits,
            default => $digits,
        };
    }

    /**
     * Cek nomor sesudah dibersihkan; dipakai importer yang tidak lewat Validator.
     */
    public static function isValidMobile(string $phone): bool
    {
        return (bool) preg_match(self::MOBILE_PATTERN, trim($phone));
    }
}
