package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.NewsDO;
import com.maikoo.superminercions.model.NoticeDO;
import lombok.Data;

@Data
public class NoticeDTO {
    private long id;
    private String text;

    public static NoticeDTO valueOf(NewsDO newsDO) {
        NoticeDTO noticeDTO = new NoticeDTO();
        noticeDTO.setId(newsDO.getId());
        noticeDTO.setText(newsDO.getTitle());
        return noticeDTO;
    }

    public static NoticeDTO valueOf(NoticeDO noticeDO){
        NoticeDTO noticeDTO = new NoticeDTO();
        noticeDTO.setId(noticeDO.getNewsId());
        noticeDTO.setText(noticeDO.getTitle());
        return noticeDTO;
    }
}
