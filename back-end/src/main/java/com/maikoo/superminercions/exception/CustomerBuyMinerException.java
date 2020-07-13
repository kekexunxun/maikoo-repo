package com.maikoo.superminercions.exception;

public class CustomerBuyMinerException extends RuntimeException{

        public CustomerBuyMinerException() {
        }

        public CustomerBuyMinerException(String message) {
            super(message);
        }

        public CustomerBuyMinerException(String message, Throwable cause) {
            super(message, cause);
        }

        public CustomerBuyMinerException(Throwable cause) {
            super(cause);
        }

        public CustomerBuyMinerException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
            super(message, cause, enableSuppression, writableStackTrace);
        }
}
