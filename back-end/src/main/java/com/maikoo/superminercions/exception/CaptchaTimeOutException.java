package com.maikoo.superminercions.exception;

public class CaptchaTimeOutException extends RuntimeException {
    public CaptchaTimeOutException() {
        super();
    }

    public CaptchaTimeOutException(String message) {
        super(message);
    }

    public CaptchaTimeOutException(String message, Throwable cause) {
        super(message, cause);
    }

    public CaptchaTimeOutException(Throwable cause) {
        super(cause);
    }

    protected CaptchaTimeOutException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
