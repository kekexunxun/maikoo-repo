package com.maikoo.superminercions.aspect;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.maikoo.superminercions.exception.InvalidParameterException;
import com.maikoo.superminercions.exception.TokenTimeOutException;
import com.maikoo.superminercions.model.AdminDO;
import com.maikoo.superminercions.model.CustomerDO;
import com.maikoo.superminercions.model.query.SettingQuery;
import com.maikoo.superminercions.util.ConstantUtil;
import org.aspectj.lang.JoinPoint;
import org.aspectj.lang.annotation.Aspect;
import org.aspectj.lang.annotation.Before;
import org.aspectj.lang.annotation.Pointcut;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.data.redis.core.RedisTemplate;
import org.springframework.stereotype.Component;
import org.springframework.util.StringUtils;

import javax.annotation.Resource;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpSession;
import java.io.IOException;

@Aspect
@Component
public class AuthenticateAspect {
    @Autowired
    private HttpServletRequest request;
    @Autowired
    private HttpSession session;
    @Resource
    private RedisTemplate<String, Object> redisTemplate;
    @Autowired
    private ObjectMapper objectMapper;

    @Pointcut("execution(* com.maikoo.superminercions.controller.front.*.*(..)) " +
            "&& !execution(* com.maikoo.superminercions.controller.front.CommonController.imageUpload(..)) " +
            "&& !execution(* com.maikoo.superminercions.controller.front.CommonController.login(..)) " +
            "&& !execution(* com.maikoo.superminercions.controller.front.CommonController.resetPassword(..))")
    public void frontAuthenticate() {
    }

    @Pointcut("execution(* com.maikoo.superminercions.controller.back.*.*(..)) " +
            "&& !execution(* com.maikoo.superminercions.controller.back.SettingController.update(..)) " +
            "&& !execution(* com.maikoo.superminercions.controller.back.AdminController.login(..))")
    public void adminAuthenticate() {
    }

    @Pointcut("execution(* com.maikoo.superminercions.controller.back.SettingController.update(..))")
    public void adminJSONParameterAuthenticate() {
    }

    @Pointcut("execution(* com.maikoo.superminercions.controller.front.CommonController.imageUpload(..))")
    public void authenticate() {
    }

    /**
     * 客户端用户认证
     */
    @Before("frontAuthenticate()")
    public void frontBefore() {
        baseAuthenticate(ConstantUtil.FRONT_ACCOUNT_TYPE);
    }

    /**
     * 管理员认证
     */
    @Before("adminAuthenticate()")
    public void adminBefore() {
        baseAuthenticate(ConstantUtil.ADMIN_ACCOUNT_TYPE);
    }

    /**
     * 管理员认证
     * 传参形式：JSON
     *
     * @param joinPoint
     */
    @Before("adminJSONParameterAuthenticate()")
    public void adminJSONParameterBefore(JoinPoint joinPoint) {
        jsonAuthenticate(joinPoint, ConstantUtil.ADMIN_ACCOUNT_TYPE);
    }

    /**
     * 用户认证，包含了客户端用户认证、管理员认证，用于不区分用户类型的接口
     */
    @Before("authenticate()")
    public void before() {
        baseAuthenticate(ConstantUtil.ALL_ACCOUNT_TYPE);
    }

    /**
     * 表单形式的传参认证
     *
     * @param accountType 用户类型
     */
    private void baseAuthenticate(int accountType) {
        String token = request.getParameter("accessToken");

        addCurrentUserSession(token, accountType);
    }

    /**
     * JSON形式的传参认证
     *
     * @param joinPoint
     */
    private void jsonAuthenticate(JoinPoint joinPoint, int accountType) {
        String token = null;

        Object[] args = joinPoint.getArgs();
        for (Object arg : args) {
            if (arg instanceof SettingQuery) {
                SettingQuery settingQuery = (SettingQuery) arg;
                token = settingQuery.getAccessToken();
                break;
            }
        }

        addCurrentUserSession(token, accountType);
    }

    /**
     * 在当前会话中，添加当前用户的信息，并且通过用户的类型来添加对应的信息。
     * 用户类型有两种形式：
     * 一种是指定用户类型，
     * 另外一种是按照用户信息里面所提供的用户类型。
     *
     * @param token       用户令牌
     * @param accountType 用户类型
     */
    private void addCurrentUserSession(String token, int accountType) {
        if (StringUtils.isEmpty(token)) {
            throw new InvalidParameterException("参数错误");
        }

        Long expire = redisTemplate.getExpire(token);
        if (expire <= 0) {
            throw new TokenTimeOutException("令牌过期");
        }

        String tokenValue = (String) redisTemplate.opsForValue().get(token);

        JsonNode jsonNode = null;
        try {
            jsonNode = objectMapper.readValue(tokenValue, JsonNode.class);

            JsonNode accountTypeNode = jsonNode.get("account_type");
            int realAccountType = accountTypeNode.asInt();

            JsonNode userNode = jsonNode.get("user_do");
            String userJSON = userNode.toString();

            if (accountType != ConstantUtil.ALL_ACCOUNT_TYPE && realAccountType != accountType) {
                throw new RuntimeException("用户类型不匹配");
            }

            if (accountType == ConstantUtil.FRONT_ACCOUNT_TYPE) {
                CustomerDO customerDO = objectMapper.readValue(userJSON, CustomerDO.class);
                session.setAttribute("current_customer", customerDO);
            }

            if (accountType == ConstantUtil.ADMIN_ACCOUNT_TYPE) {
                AdminDO adminDO = objectMapper.readValue(userJSON, AdminDO.class);
                session.setAttribute("current_admin", adminDO);
            }

        } catch (IOException e) {
            throw new RuntimeException(e);
        }
    }
}
