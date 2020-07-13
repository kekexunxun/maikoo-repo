package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.NoticeDO;
import lombok.Data;

@Data
public class NoticeBackDTO {
    private long id;

    public static NoticeBackDTO valueOf(NoticeDO noticeDO){
        NoticeBackDTO noticeBackDTO = new NoticeBackDTO();
        noticeBackDTO.setId(noticeDO.getNewsId());
        return noticeBackDTO;
    }
}
