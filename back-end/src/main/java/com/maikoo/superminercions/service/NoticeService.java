package com.maikoo.superminercions.service;

import com.maikoo.superminercions.model.dto.NoticeBackDTO;
import com.maikoo.superminercions.model.query.NoticeQuery;

public interface NoticeService {

    void update(NoticeQuery noticeQuery);

    NoticeBackDTO information();
}
