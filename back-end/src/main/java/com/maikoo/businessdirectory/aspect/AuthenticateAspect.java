package com.maikoo.businessdirectory.aspect;

import com.fasterxml.jackson.databind.ObjectMapper;
import com.maikoo.businessdirectory.exception.TokenTimeOutException;
import com.maikoo.businessdirectory.model.AdminDO;
import com.maikoo.businessdirectory.model.UserDO;
import com.maikoo.businessdirectory.util.RedisUtil;
import lombok.extern.slf4j.Slf4j;
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
import java.security.InvalidParameterException;

@Slf4j
@Aspect
@Component
public class AuthenticateAspect {
    @Autowired
    private HttpServletRequest request;
    @Autowired
    private HttpSession session;
    @Autowired
    private RedisUtil redisUtil;
    @Resource
    private RedisTemplate<String, Object> redisTemplate;
    @Autowired
    private ObjectMapper objectMapper;

    @Pointcut("execution(* com.maikoo.businessdirectory.controller.front.*.*(..)) " +
            "&& !execution(* com.maikoo.businessdirectory.controller.front.UserController.login(..))")
    public void frontAuthenticate() {
    }

    @Pointcut("execution(* com.maikoo.businessdirectory.controller.back.*.*(..)) " +
            "&& !execution(* com.maikoo.businessdirectory.controller.back.AdminController.login(..))")
    public void backAuthenticate() {
    }

    @Pointcut("execution(* com.maikoo.businessdirectory.controller.common.*.*(..))")
    public void authenticate() {
    }

    /**
     * 客户端用户认证
     */
    @Before("frontAuthenticate()")
    public void frontBefore() {
        UserDO userDO = (UserDO) base();
        session.setAttribute("current_user", userDO);
    }

    /**
     * 后台用户认证
     */
    @Before("backAuthenticate()")
    public void adminBefore() {
        AdminDO adminDO = (AdminDO) base();
        session.setAttribute("current_admin", adminDO);
    }

    /**
     * 通用认证
     */
    @Before("authenticate()")
    public void before() {
        base();
    }

    /**
     * 获取token所对应的数据
     *
     * @return
     */
    Object base() {
        String token = request.getHeader("authentication");

        log.info("aop token: {}", token);

        if (StringUtils.isEmpty(token)) {
            throw new InvalidParameterException("参数错误");
        }

        Long expire = redisTemplate.getExpire(token);
        if (expire <= 0) {
            throw new TokenTimeOutException("令牌过期");
        }

        return redisTemplate.opsForValue().get(token);
    }
}
