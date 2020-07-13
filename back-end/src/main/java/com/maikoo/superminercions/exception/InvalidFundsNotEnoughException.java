package com.maikoo.superminercions.exception;

public class InvalidFundsNotEnoughException extends RuntimeException {


    public InvalidFundsNotEnoughException() {
    }

    public InvalidFundsNotEnoughException(String message) {
        super(message);
    }

    public InvalidFundsNotEnoughException(String message, Throwable cause) {
        super(message, cause);
    }

    public InvalidFundsNotEnoughException(Throwable cause) {
        super(cause);
    }

    public InvalidFundsNotEnoughException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }

}
