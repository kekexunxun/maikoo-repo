package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.NewsDO;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.TimeUtil;
import lombok.Data;

import java.time.LocalDateTime;

@Data
public class NewsDTO {
    private long newsId;
    private String newsDate;
    private String newsTitle;
    private String newsContent;

    public static NewsDTO valueOf(NewsDO newsDO){
        LocalDateTime localDateTime = TimeUtil.timeStampToDateTime(newsDO.getCreatedAt());
        NewsDTO newsDTO = new NewsDTO();
        newsDTO.setNewsId(newsDO.getId());
        newsDTO.setNewsTitle(newsDO.getTitle());
        newsDTO.setNewsDate(localDateTime.format(ConstantUtil.BASE_DATE_TIME_PATTERN));
        newsDTO.setNewsContent(newsDO.getContent());
        return newsDTO;
    }
}
