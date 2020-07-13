package com.maikoo.superminercions.util;

import com.google.common.base.Charsets;
import com.google.common.hash.Hashing;

import javax.crypto.SecretKey;
import javax.crypto.spec.SecretKeySpec;

public class EncryptUtil {
    private static final SecretKey PASSWORD_KEY = new SecretKeySpec("vxx1A1JWJlx2".getBytes(Charsets.UTF_8), "HmacMD5");
    private static final SecretKey TOKEN_KEY = new SecretKeySpec("hnFPmf6sIxac".getBytes(Charsets.UTF_8), "HmacMD5");

    public static String password(String value) {
        return hmacMd5(PASSWORD_KEY, value);
    }

    public static String token(String value) {
        return hmacMd5(TOKEN_KEY, value);
    }

    private static String hmacMd5(SecretKey secretKey, String value) {
        return Hashing.hmacMd5(secretKey).hashString(value, Charsets.UTF_8).toString();
    }
}
