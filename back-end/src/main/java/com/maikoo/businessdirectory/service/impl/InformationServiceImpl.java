package com.maikoo.businessdirectory.service.impl;

import com.maikoo.businessdirectory.dao.*;
import com.maikoo.businessdirectory.model.UserDO;
import com.maikoo.businessdirectory.model.dto.FlagDTO;
import com.maikoo.businessdirectory.service.InformationService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import javax.servlet.http.HttpSession;

@Service
public class InformationServiceImpl implements InformationService {

    @Autowired
    private MessageDao messageDao;
    @Autowired
    private ClassUserApplyDao classUserApplyDao;
    @Autowired
    private CommunityUserApplyDao communityUserApplyDao;
    @Autowired
    private SchoolUserApplyDao schoolUserApplyDao;
    @Autowired
    private CountryUserApplyDao countryUserApplyDao;

    @Autowired
    private UserDao userDao;

    @Autowired
    private HttpSession session;

    @Override
    public FlagDTO userHasNewMessage() {
        UserDO userDO = (UserDO) session.getAttribute("current_user");
        FlagDTO flagDTO = new FlagDTO();
        flagDTO.setTbHasUnread(classUserApplyDao.userHasNewMessage(userDO.getUserId()));
        flagDTO.setSqHasUnread(communityUserApplyDao.userHasNewMessage(userDO.getUserId()));
        flagDTO.setXyHasUnread(schoolUserApplyDao.userHasNewMessage(userDO.getUserId()));
        flagDTO.setTxHasUnread(countryUserApplyDao.userHasNewMessage(userDO.getUserId()));
        userDao.updateApplyRequestAt(userDO.getUserId());
        return flagDTO;
    }

    @Override
    public FlagDTO adminHasNewMessage() {
        UserDO userDO = (UserDO) session.getAttribute("current_user");
        FlagDTO flagDTO = new FlagDTO();
        flagDTO.setTbHasUnread(classUserApplyDao.adminHasNewMessage(userDO.getUserId()));
        flagDTO.setSqHasUnread(communityUserApplyDao.adminHasNewMessage(userDO.getUserId()));
        flagDTO.setXyHasUnread(schoolUserApplyDao.adminHasNewMessage(userDO.getUserId()));
        flagDTO.setTxHasUnread(countryUserApplyDao.adminHasNewMessage(userDO.getUserId()));
        userDao.updateReviewRequestAt(userDO.getUserId());
        return flagDTO;
    }

    @Override
    public FlagDTO hasNewMessage() {
        UserDO userDO = (UserDO) session.getAttribute("current_user");
        boolean flag  = messageDao.hasNewMessage(userDO.getUserId());
        FlagDTO flagDTO = new FlagDTO();
        flagDTO.setHasNewMsg(flag);
        userDao.updateMessageRequestAt(userDO.getUserId());
        return flagDTO;
    }
}
