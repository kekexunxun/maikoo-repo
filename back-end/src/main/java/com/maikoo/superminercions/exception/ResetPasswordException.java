package com.maikoo.superminercions.exception;

public class ResetPasswordException extends RuntimeException {
    public ResetPasswordException() {
    }

    public ResetPasswordException(String message) {
        super(message);
    }

    public ResetPasswordException(String message, Throwable cause) {
        super(message, cause);
    }

    public ResetPasswordException(Throwable cause) {
        super(cause);
    }

    public ResetPasswordException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
