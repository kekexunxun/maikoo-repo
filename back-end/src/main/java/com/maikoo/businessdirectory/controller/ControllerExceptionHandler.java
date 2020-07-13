package com.maikoo.businessdirectory.controller;

import com.maikoo.businessdirectory.exception.TokenTimeOutException;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import lombok.extern.slf4j.Slf4j;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.ResponseBody;

@Slf4j
@ControllerAdvice
public class ControllerExceptionHandler {
    @ExceptionHandler(value = TokenTimeOutException.class)
    @ResponseBody
    public ResponseDTO tokenTimeOutException(Exception e) {
        log.error("用户登录凭证过期", e);
        return new ResponseDTO(1001, "用户登录凭证过期");
    }

    @ExceptionHandler(value = Exception.class)
    @ResponseBody
    public ResponseDTO<Exception> handlerError(Exception e) {
        log.error("系统错误", e);
        return new ResponseDTO<>(400, "请求失败", e);
    }
}
