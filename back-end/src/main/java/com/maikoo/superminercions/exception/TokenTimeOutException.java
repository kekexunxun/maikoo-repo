package com.maikoo.superminercions.exception;

public class TokenTimeOutException extends RuntimeException {

    public TokenTimeOutException() {
    }

    public TokenTimeOutException(String message) {
        super(message);
    }

    public TokenTimeOutException(String message, Throwable cause) {
        super(message, cause);
    }

    public TokenTimeOutException(Throwable cause) {
        super(cause);
    }

    public TokenTimeOutException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
