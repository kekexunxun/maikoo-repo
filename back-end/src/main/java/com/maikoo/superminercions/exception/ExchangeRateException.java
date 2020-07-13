package com.maikoo.superminercions.exception;

public class ExchangeRateException extends RuntimeException {
    public ExchangeRateException() {
    }

    public ExchangeRateException(String message) {
        super(message);
    }

    public ExchangeRateException(String message, Throwable cause) {
        super(message, cause);
    }

    public ExchangeRateException(Throwable cause) {
        super(cause);
    }

    public ExchangeRateException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
