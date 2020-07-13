package com.maikoo.superminercions.exception;

public class DuplicatePhoneException extends RuntimeException {
    public DuplicatePhoneException() {
    }

    public DuplicatePhoneException(String message) {
        super(message);
    }

    public DuplicatePhoneException(String message, Throwable cause) {
        super(message, cause);
    }

    public DuplicatePhoneException(Throwable cause) {
        super(cause);
    }

    public DuplicatePhoneException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
