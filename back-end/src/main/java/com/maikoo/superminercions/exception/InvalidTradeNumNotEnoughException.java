package com.maikoo.superminercions.exception;

public class InvalidTradeNumNotEnoughException extends RuntimeException {


    public InvalidTradeNumNotEnoughException() {
    }

    public InvalidTradeNumNotEnoughException(String message) {
        super(message);
    }

    public InvalidTradeNumNotEnoughException(String message, Throwable cause) {
        super(message, cause);
    }

    public InvalidTradeNumNotEnoughException(Throwable cause) {
        super(cause);
    }

    public InvalidTradeNumNotEnoughException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }

}
