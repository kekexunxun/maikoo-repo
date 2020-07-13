package com.maikoo.businessdirectory.service.impl;

import com.github.pagehelper.PageHelper;
import com.maikoo.businessdirectory.config.CustomEnvironmentConfig;
import com.maikoo.businessdirectory.dao.MessageDao;
import com.maikoo.businessdirectory.model.MessageDO;
import com.maikoo.businessdirectory.model.UserDO;
import com.maikoo.businessdirectory.model.dto.MessageDTO;
import com.maikoo.businessdirectory.service.MessageService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.util.CollectionUtils;

import javax.servlet.http.HttpSession;
import java.util.ArrayList;
import java.util.List;

@Service
public class MessageServiceImpl implements MessageService {
    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;
    @Autowired
    private MessageDao messageDao;
    @Autowired
    private HttpSession session;

    @Override
    public List<MessageDTO> listForUser(int pageNumber) {
        UserDO userDO = (UserDO) session.getAttribute("current_user");

        PageHelper.startPage(pageNumber > 0 ? pageNumber : customEnvironmentConfig.getPageNumber(), customEnvironmentConfig.getPerPage());
        List<Long> ids = messageDao.selectIdsByUserId(userDO.getUserId());

        List<MessageDTO> messageDTOList = new ArrayList<>();
        if (!CollectionUtils.isEmpty(ids)) {
            List<MessageDO> messageDOList = messageDao.selectByIds(ids);
            messageDOList.forEach(messageDO -> messageDTOList.add(MessageDTO.valueOf(messageDO)));
        }
        return messageDTOList;
    }


}
