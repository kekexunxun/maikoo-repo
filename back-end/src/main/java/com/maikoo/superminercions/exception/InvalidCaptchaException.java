package com.maikoo.superminercions.exception;

public class InvalidCaptchaException extends RuntimeException {
    public InvalidCaptchaException() {
        super();
    }

    public InvalidCaptchaException(String message) {
        super(message);
    }

    public InvalidCaptchaException(String message, Throwable cause) {
        super(message, cause);
    }

    public InvalidCaptchaException(Throwable cause) {
        super(cause);
    }

    protected InvalidCaptchaException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
