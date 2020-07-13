package com.maikoo.businessdirectory.service.impl;

import com.maikoo.businessdirectory.dao.UserDao;
import com.maikoo.businessdirectory.model.UserDO;
import com.maikoo.businessdirectory.model.dto.PhoneDTO;
import com.maikoo.businessdirectory.model.dto.TokenDTO;
import com.maikoo.businessdirectory.model.dto.UserDTO;
import com.maikoo.businessdirectory.model.query.PhoneDecryptQuery;
import com.maikoo.businessdirectory.model.query.UserQuery;
import com.maikoo.businessdirectory.service.UserService;
import com.maikoo.businessdirectory.util.EncryptUtil;
import com.maikoo.businessdirectory.util.RedisUtil;
import com.maikoo.businessdirectory.util.WechatUtil;
import lombok.extern.slf4j.Slf4j;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.data.redis.core.RedisTemplate;
import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;

import javax.annotation.Resource;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpSession;
import java.security.InvalidParameterException;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.TimeUnit;

@Slf4j
@Service
public class UserServiceImpl implements UserService {
    @Resource
    private RedisTemplate<String, Object> redisTemplate;
    @Autowired
    private UserDao userDao;
    @Autowired
    private WechatUtil weChatUtil;
    @Autowired
    private RedisUtil redisUtil;
    @Autowired
    private HttpSession session;
    @Autowired
    private HttpServletRequest request;

    @Override
    public TokenDTO login(String code) {
        UserDO userDO = weChatUtil.login(code);

        if (userDO == null) {
            throw new InvalidParameterException();
        }

        UserDO oldUserDO = userDao.selectByOpenId(userDO.getOpenid());

        if (oldUserDO == null) {
            userDao.insert(userDO);
        } else {
            userDO.setUserId(oldUserDO.getUserId());
            userDao.update(userDO);
        }

        TokenDTO tokenDTO = new TokenDTO();
        tokenDTO.setAccessToken(EncryptUtil.token(userDO.getOpenid() + System.currentTimeMillis()));
        redisTemplate.opsForValue().set(tokenDTO.getAccessToken(), userDO);
        redisTemplate.expire(tokenDTO.getAccessToken(), 30, TimeUnit.DAYS);

        return tokenDTO;
    }

    @Override
    public void updateAuthentication(UserQuery userQuery) {
        UserDO currentUser = (UserDO) session.getAttribute("current_user");

        UserDO userDO = new UserDO();
        userDO.setUserId(currentUser.getUserId());
        userDO.setNickname(userQuery.getNickName());
        userDO.setAvatarUrl(userQuery.getAvatarUrl());
        userDO.setIsAuth(Boolean.TRUE);
        userDao.update(userDO);
    }

    @Override
    public PhoneDTO phoneDecrypt(PhoneDecryptQuery phoneDecryptQuery) {
        PhoneDTO phoneDTO = null;

        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        String phone = currentUserDO.getMobile();

        if(StringUtils.isEmpty(phone)){
            phone = userDao.selectOne(currentUserDO.getUserId()).getMobile();
        }

        if(StringUtils.isEmpty(phone)){
            try {
                phone = weChatUtil.phoneEncrypt(phoneDecryptQuery);
            }catch (Exception e){
                if (!StringUtils.isEmpty(phoneDecryptQuery.getCode())) {
                    UserDO userDO = weChatUtil.login(phoneDecryptQuery.getCode());

                    currentUserDO.setOpenid(userDO.getOpenid());
                    currentUserDO.setUnionId(userDO.getUnionId());
                    currentUserDO.setSessionKey(userDO.getSessionKey());

                    session.setAttribute("current_user", currentUserDO);

                    userDO.setUserId(currentUserDO.getUserId());
                    userDao.update(userDO);

                    String authentication = request.getHeader("authentication");

                    log.info("token: {}", authentication);

                    if(!StringUtils.isEmpty(authentication)){
                        redisUtil.updateValue(authentication, currentUserDO);
                    }
                }

                phone = weChatUtil.phoneEncrypt(phoneDecryptQuery);
            }

            UserDO updatePhoneUserDO = new UserDO();
            updatePhoneUserDO.setMobile(phone);
            updatePhoneUserDO.setUserId(currentUserDO.getUserId());
            userDao.update(updatePhoneUserDO);
        }

        phoneDTO = new PhoneDTO();
        phoneDTO.setMobile(phone);
        return phoneDTO;
    }

    @Override
    public UserDTO information() {
        UserDTO userDTO = null;
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        UserDO userDO = userDao.selectOne(currentUserDO.getUserId());

        log.info("session user: {} user: {}", currentUserDO, userDO);

        if (userDO.getIsAuth() != null && userDO.getIsAuth()) {
            userDTO = new UserDTO();
            userDTO.setNickname(userDO.getNickname());
            userDTO.setAvatarUrl(userDO.getAvatarUrl());
        }
        return userDTO;
    }

    @Override
    public List<UserDTO> getUserList() {
        List<UserDO> userDOList = userDao.selectUserList();
        List<UserDTO> userDTOList = new ArrayList<>();
        userDOList.forEach(userDO ->userDTOList.add(UserDTO.valueOf(userDO)));
        return userDTOList;
    }

}
