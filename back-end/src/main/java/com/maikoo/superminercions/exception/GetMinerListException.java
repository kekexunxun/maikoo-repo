package com.maikoo.superminercions.exception;

public class GetMinerListException extends RuntimeException{

        public GetMinerListException() {
        }

        public GetMinerListException(String message) {
            super(message);
        }

        public GetMinerListException(String message, Throwable cause) {
            super(message, cause);
        }

        public GetMinerListException(Throwable cause) {
            super(cause);
        }

        public GetMinerListException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
            super(message, cause, enableSuppression, writableStackTrace);
        }
}
