package com.maikoo.superminercions.exception;

public class GetTradingRecordExcption extends RuntimeException{

        public GetTradingRecordExcption() {
        }

        public GetTradingRecordExcption(String message) {
            super(message);
        }

        public GetTradingRecordExcption(String message, Throwable cause) {
            super(message, cause);
        }

        public GetTradingRecordExcption(Throwable cause) {
            super(cause);
        }

        public GetTradingRecordExcption(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
            super(message, cause, enableSuppression, writableStackTrace);
        }
}
