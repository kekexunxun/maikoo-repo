package com.maikoo.superminercions.exception;

public class InvalidTradingPasswordException extends RuntimeException {
    public InvalidTradingPasswordException() {
    }

    public InvalidTradingPasswordException(String message) {
        super(message);
    }

    public InvalidTradingPasswordException(String message, Throwable cause) {
        super(message, cause);
    }

    public InvalidTradingPasswordException(Throwable cause) {
        super(cause);
    }

    public InvalidTradingPasswordException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
