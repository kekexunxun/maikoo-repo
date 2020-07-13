package com.maikoo.businessdirectory.exception;

public class ImageFormatException extends RuntimeException {
    public ImageFormatException() {
    }

    public ImageFormatException(String message) {
        super(message);
    }

    public ImageFormatException(String message, Throwable cause) {
        super(message, cause);
    }

    public ImageFormatException(Throwable cause) {
        super(cause);
    }

    public ImageFormatException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
