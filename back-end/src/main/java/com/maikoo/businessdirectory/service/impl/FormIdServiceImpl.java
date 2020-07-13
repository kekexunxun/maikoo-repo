package com.maikoo.businessdirectory.service.impl;

import com.maikoo.businessdirectory.dao.FormIdDao;
import com.maikoo.businessdirectory.model.FormIdDO;
import com.maikoo.businessdirectory.model.UserDO;
import com.maikoo.businessdirectory.model.query.FormIdQuery;
import com.maikoo.businessdirectory.service.FormIdService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.util.CollectionUtils;

import javax.servlet.http.HttpSession;
import java.util.List;

@Service
public class FormIdServiceImpl implements FormIdService {
    @Autowired
    private FormIdDao formIdDao;
    @Autowired
    private HttpSession session;

    @Override
    @Transactional
    public void insert(List<FormIdQuery> formIdQueryList) {
        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        if (!CollectionUtils.isEmpty(formIdQueryList)) {
            formIdQueryList.forEach(formIdQuery -> {
                FormIdDO formIdDO = FormIdDO.valueOf(formIdQuery);
                formIdDO.setUserId(currentUserDO.getUserId());
                formIdDao.insert(formIdDO);
            });
        }
    }
}
