package com.maikoo.superminercions.exception;

public class CustomerInformationException extends RuntimeException {
    public CustomerInformationException() {
    }

    public CustomerInformationException(String message) {
        super(message);
    }

    public CustomerInformationException(String message, Throwable cause) {
        super(message, cause);
    }

    public CustomerInformationException(Throwable cause) {
        super(cause);
    }

    public CustomerInformationException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
