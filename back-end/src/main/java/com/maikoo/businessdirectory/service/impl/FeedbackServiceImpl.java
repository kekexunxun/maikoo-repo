package com.maikoo.businessdirectory.service.impl;

import com.maikoo.businessdirectory.dao.FeedbackDao;
import com.maikoo.businessdirectory.model.FeedbackDO;
import com.maikoo.businessdirectory.model.UserDO;
import com.maikoo.businessdirectory.model.dto.FeedbackDTO;
import com.maikoo.businessdirectory.model.query.FeedbackQuery;
import com.maikoo.businessdirectory.service.FeedbackService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.util.CollectionUtils;

import javax.servlet.http.HttpSession;
import java.util.ArrayList;
import java.util.List;

@Service
public class FeedbackServiceImpl implements FeedbackService {
    @Autowired
    private FeedbackDao feedbackDao;

    @Autowired
    private HttpSession session;

    @Override
    public void insert(FeedbackQuery feedbackQuery) {
        FeedbackDO feedbackDO = FeedbackDO.valueOf(feedbackQuery);
        UserDO userDO = (UserDO) session.getAttribute("current_user");
        feedbackDO.setUserDO(userDO);
        feedbackDao.insert(feedbackDO);
    }

    @Override
    public List<FeedbackDTO> adminGetFeedBack() {
        List<FeedbackDTO> feedbackDTOList = new ArrayList<>();
        List<FeedbackDO> feedbackDOList = feedbackDao.adminGetFeedBackList();
        if (!CollectionUtils.isEmpty(feedbackDOList)) {
            feedbackDOList.forEach(feedbackDO -> feedbackDTOList.add(FeedbackDTO.valueOf(feedbackDO)));
        }
        return feedbackDTOList;
    }
}
