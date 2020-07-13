package com.maikoo.superminercions.exception;

public class CustomerDisabledException extends RuntimeException {
    public CustomerDisabledException() {
    }

    public CustomerDisabledException(String message) {
        super(message);
    }

    public CustomerDisabledException(String message, Throwable cause) {
        super(message, cause);
    }

    public CustomerDisabledException(Throwable cause) {
        super(cause);
    }

    public CustomerDisabledException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
