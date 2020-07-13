package com.maikoo.superminercions.controller;

import com.maikoo.superminercions.exception.*;
import com.maikoo.superminercions.model.dto.ResponseDTO;
import org.springframework.web.HttpRequestMethodNotSupportedException;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.ResponseBody;
import org.springframework.web.multipart.MaxUploadSizeExceededException;

@ControllerAdvice
public class ControllerExceptionHandler {
    @ExceptionHandler(value = {HttpRequestMethodNotSupportedException.class})
    @ResponseBody
    public ResponseDTO requestMethodError() {
        return new ResponseDTO(401, "请求方式错误", null);
    }

    @ExceptionHandler(value = {InvalidParameterException.class})
    @ResponseBody
    public ResponseDTO parameterError() {
        return new ResponseDTO(402, "请求参数错误", null);
    }

    @ExceptionHandler(value = {MaxUploadSizeExceededException.class})
    @ResponseBody
    public ResponseDTO uploadError() {
        return new ResponseDTO(700, "图片大小不符合", null);
    }

    @ExceptionHandler(value = {ImageFormatException.class})
    @ResponseBody
    public ResponseDTO imageFormatError() {
        return new ResponseDTO(701, "图片格式错误", null);
    }

    @ExceptionHandler(value = {InvalidCaptchaException.class})
    @ResponseBody
    public ResponseDTO captchaError() {
        return new ResponseDTO(800, "短信验证码错误", null);
    }

    @ExceptionHandler(value = {CaptchaTimeOutException.class})
    @ResponseBody
    public ResponseDTO captchaTimeOutError() {
        return new ResponseDTO(801, "短信验证码已过期", null);
    }

    @ExceptionHandler(value = {UsedCaptchaException.class})
    @ResponseBody
    public ResponseDTO usedCaptchaError() {
        return new ResponseDTO(802, "短信验证码不可用", null);
    }

    @ExceptionHandler(value = {DiscordPhoneException.class})
    @ResponseBody
    public ResponseDTO discordPhoneError(){
        return new ResponseDTO(403, "手机号码错误", null);
    }

    @ExceptionHandler(value = {TokenTimeOutException.class})
    @ResponseBody
    public ResponseDTO tokenTimeOutError() {
        return new ResponseDTO(1001, "用户登录凭证过期", null);
    }

    @ExceptionHandler(value = {InvalidUsernameOrPasswordException.class})
    @ResponseBody
    public ResponseDTO usernameOrPasswordError() {
        return new ResponseDTO(1002, "账号或密码错误", null);
    }

    @ExceptionHandler(value = {SamePasswordException.class})
    @ResponseBody
    public ResponseDTO samePasswordError() {
        return new ResponseDTO(1003, "新密码与原密码一致", null);
    }

    @ExceptionHandler(value = {UpdatePasswordException.class})
    @ResponseBody
    public ResponseDTO updatePasswordError() {
        return new ResponseDTO(1004, "密码修改失败", null);
    }

    @ExceptionHandler(value = {ResetPasswordException.class})
    @ResponseBody
    public ResponseDTO resetPasswordError() {
        return new ResponseDTO(1005, "密码重置失败", null);
    }

    @ExceptionHandler(value = {DuplicatePhoneException.class})
    @ResponseBody
    public ResponseDTO duplicatePhoneError() {
        return new ResponseDTO(1006, "手机号已存在", null);
    }

    @ExceptionHandler(value = {CustomerDisabledException.class})
    @ResponseBody
    public ResponseDTO customerDisabledError() {
        return new ResponseDTO(1007, "帐号已被禁用", null);
    }

    @ExceptionHandler(value = {InvalidTradingPasswordException.class})
    @ResponseBody
    public ResponseDTO invalidTradingPasswordError() {
        return new ResponseDTO(1101, "交易密码错误", null);
    }

    @ExceptionHandler(value = {ExchangeRateException.class})
    @ResponseBody
    public ResponseDTO exchangeRateError() {
        return new ResponseDTO(1102, "汇率获取失败", null);
    }

    @ExceptionHandler(value = {CustomerInformationException.class})
    @ResponseBody
    public ResponseDTO customerInformationError() {
        return new ResponseDTO(1201, "汇率获取失败", null);
    }

    @ExceptionHandler(value = {UpdateCustomerInformationException.class})
    @ResponseBody
    public ResponseDTO updateCustomerInformationError() {
        return new ResponseDTO(1202, "汇率获取失败", null);
    }

    /**
     * 余额不足=>兑换ETH、SMC提现、SMC卖出、ETH卖出、兑换SMC
     * @return
     */
    @ExceptionHandler(value = {InvalidFundsNotEnoughException.class})
    @ResponseBody
    public ResponseDTO fundsNotEnoughError() {
        return new ResponseDTO(2001, "余额不足", null);
    }
    /**
     * 数量不满足条件=>兑换ETH、SMC交易
     * @return
     */
    @ExceptionHandler(value = {InvalidTradeNumNotEnoughException.class})
    @ResponseBody
    public ResponseDTO numberNotEnoughError() {
        return new ResponseDTO(2002, "数量不满足条件", null);
    }

    /**
     *SMC、ETH交易记录
     * @return
     */
    @ExceptionHandler(value = {GetTradingRecordExcption.class})
    @ResponseBody
    public ResponseDTO getTradingRecordError() {
        return new ResponseDTO(3001, "获取交易记录失败", null);
    }

    /**
     *SMC、ETH交易记录
     * @return
     */
    @ExceptionHandler(value = {GetTradingRecordDetailException.class})
    @ResponseBody
    public ResponseDTO getTradingRecordDetailError() {
        return new ResponseDTO(3002, "获取交易记录详情失败", null);
    }

    /**
     *用户矿机、系统矿机
     * @return
     */
    @ExceptionHandler(value = {GetMinerListException.class})
    @ResponseBody
    public ResponseDTO getMinerListError() {
        return new ResponseDTO(4101, "矿机列表获取失败", null);
    }

    /**
     * 用户矿机、系统矿机
     * @return
     */
    @ExceptionHandler(value = {GetMinerDetailException.class})
    @ResponseBody
    public ResponseDTO getMinerDetailError() {
        return new ResponseDTO(4102, "矿机详情获取失败", null);
    }

    /**
     *用户矿机
     * @return
     */
    @ExceptionHandler(value = {CustomerBuyMinerException.class})
    @ResponseBody
    public ResponseDTO customerBuyMinerError() {
        return new ResponseDTO(4103, "用户矿机申请购买失败", null);
    }

    @ExceptionHandler(value = Exception.class)
    @ResponseBody
    public ResponseDTO<Exception> handlerError(Exception e) {
        return new ResponseDTO<>(400, "请求失败", e);
    }
}
