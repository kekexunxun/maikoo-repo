package com.maikoo.superminercions.service.impl;

import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.maikoo.superminercions.dao.AdminDao;
import com.maikoo.superminercions.exception.InvalidUsernameOrPasswordException;
import com.maikoo.superminercions.exception.SamePasswordException;
import com.maikoo.superminercions.exception.UpdatePasswordException;
import com.maikoo.superminercions.model.AccountDO;
import com.maikoo.superminercions.model.AdminDO;
import com.maikoo.superminercions.model.dto.LoginDTO;
import com.maikoo.superminercions.service.AdminService;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.EncryptUtil;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.data.redis.core.RedisTemplate;
import org.springframework.stereotype.Service;

import javax.annotation.Resource;
import javax.servlet.http.HttpSession;
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
    public LoginDTO login(String username, String password) {
        AdminDO adminDO = adminDao.login(username, EncryptUtil.password(password));

        if (adminDO == null) {
            throw new InvalidUsernameOrPasswordException("用户名或密码错误");
        }

        LoginDTO loginDTO = new LoginDTO();
        loginDTO.setAccessToken(EncryptUtil.token(username + System.currentTimeMillis()));
        try {
            AccountDO<AdminDO> adminAccountDO = new AccountDO<>();
            adminAccountDO.setAccountType(ConstantUtil.ADMIN_ACCOUNT_TYPE);
            adminAccountDO.setUserDO(adminDO);
            redisTemplate.opsForValue().set(loginDTO.getAccessToken(), objectMapper.writeValueAsString(adminAccountDO));
            redisTemplate.expire(loginDTO.getAccessToken(), 2, TimeUnit.HOURS);
        } catch (JsonProcessingException e) {
            throw new RuntimeException(e);
        }

        return loginDTO;
    }

    @Override
    public void updatePassword(String phone, String password) {
        AdminDO currentAdminDO = (AdminDO) session.getAttribute("current_admin");

        if (adminDao.checkOldPassword(currentAdminDO.getId(), EncryptUtil.password(password))) {
            throw new SamePasswordException();
        }

        currentAdminDO.setPassword(EncryptUtil.password(password));

        if(adminDao.updatePassword(currentAdminDO) == 0){
            throw new UpdatePasswordException();
        }
    }
}
