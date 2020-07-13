package com.maikoo.superminercions.exception;

public class UploadException extends RuntimeException {

    public UploadException() {
        super();
    }

    public UploadException(String message) {
        super(message);
    }

    public UploadException(String message, Throwable cause) {
        super(message, cause);
    }

    public UploadException(Throwable cause) {
        super(cause);
    }

    protected UploadException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
