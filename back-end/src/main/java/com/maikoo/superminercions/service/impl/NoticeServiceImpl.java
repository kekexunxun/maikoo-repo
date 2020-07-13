package com.maikoo.superminercions.service.impl;

import com.maikoo.superminercions.dao.NoticeDao;
import com.maikoo.superminercions.model.NoticeDO;
import com.maikoo.superminercions.model.dto.NoticeBackDTO;
import com.maikoo.superminercions.model.query.NoticeQuery;
import com.maikoo.superminercions.service.NoticeService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

@Service
public class NoticeServiceImpl implements NoticeService {

    @Autowired
    private NoticeDao noticeDao;

    @Override
    public void update(NoticeQuery noticeQuery) {
        NoticeDO noticeDO = new NoticeDO();
        noticeDO.setId(Long.valueOf(1));
        noticeDO.setNewsId(noticeQuery.getNavId());
        noticeDO.setTitle(noticeQuery.getText());
        noticeDao.update(noticeDO);
    }

    @Override
    public NoticeBackDTO information() {
        NoticeDO noticeDO = noticeDao.selectOne(1);
        return NoticeBackDTO.valueOf(noticeDO);
    }
}
