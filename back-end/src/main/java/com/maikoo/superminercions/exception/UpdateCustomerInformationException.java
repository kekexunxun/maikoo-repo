package com.maikoo.superminercions.exception;

public class UpdateCustomerInformationException extends RuntimeException {
    public UpdateCustomerInformationException() {
    }

    public UpdateCustomerInformationException(String message) {
        super(message);
    }

    public UpdateCustomerInformationException(String message, Throwable cause) {
        super(message, cause);
    }

    public UpdateCustomerInformationException(Throwable cause) {
        super(cause);
    }

    public UpdateCustomerInformationException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
