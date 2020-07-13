package com.maikoo.superminercions.exception;

public class UsedCaptchaException extends RuntimeException {
    public UsedCaptchaException() {
    }

    public UsedCaptchaException(String message) {
        super(message);
    }

    public UsedCaptchaException(String message, Throwable cause) {
        super(message, cause);
    }

    public UsedCaptchaException(Throwable cause) {
        super(cause);
    }

    public UsedCaptchaException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
