package com.maikoo.superminercions.exception;

public class GetMinerDetailException extends RuntimeException{

        public GetMinerDetailException() {
        }

        public GetMinerDetailException(String message) {
            super(message);
        }

        public GetMinerDetailException(String message, Throwable cause) {
            super(message, cause);
        }

        public GetMinerDetailException(Throwable cause) {
            super(cause);
        }

        public GetMinerDetailException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
            super(message, cause, enableSuppression, writableStackTrace);
        }
}
