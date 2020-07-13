package com.maikoo.businessdirectory.service.impl;

import com.fasterxml.jackson.databind.ObjectMapper;
import com.maikoo.businessdirectory.dao.AdminDao;
import com.maikoo.businessdirectory.model.AdminDO;
import com.maikoo.businessdirectory.model.dto.TokenDTO;
import com.maikoo.businessdirectory.service.AdminService;
import com.maikoo.businessdirectory.util.EncryptUtil;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.data.redis.core.RedisTemplate;
import org.springframework.stereotype.Service;

import javax.annotation.Resource;
import javax.servlet.http.HttpSession;
import java.security.InvalidParameterException;
import java.util.concurrent.TimeUnit;

@Service
public class AdminServiceImpl implements AdminService {
    @Resource
    private RedisTemplate<String, Object> redisTemplate;
    @Autowired
    private AdminDao adminDao;
    @Autowired
    private HttpSession session;
    @Autowired
    private ObjectMapper objectMapper;

    @Override
    public TokenDTO login(String username, String password) {
        AdminDO adminDO = adminDao.selectByUsernameAndPassword(username, EncryptUtil.password(password));

        if (adminDO == null) {
            throw new InvalidParameterException("用户名或密码错误");
        }

        TokenDTO tokenDTO = new TokenDTO();
        tokenDTO.setAccessToken(EncryptUtil.token(username + System.currentTimeMillis()));
        redisTemplate.opsForValue().set(tokenDTO.getAccessToken(), adminDO);
        redisTemplate.expire(tokenDTO.getAccessToken(), 2, TimeUnit.HOURS);

        return tokenDTO;
    }

    @Override
    public void updatePassword(String oldPassword, String newPassword) {
        AdminDO currentAdminDO = (AdminDO) session.getAttribute("current_admin");

        AdminDO adminDO = adminDao.selectOne(currentAdminDO.getAdminId());

        if (!adminDO.getPassword().equals(EncryptUtil.password(oldPassword))) {
            throw new InvalidParameterException("旧密码不一致");
        }

        adminDO.setPassword(EncryptUtil.password(newPassword));
        adminDao.updatePassword(adminDO);
    }
}
