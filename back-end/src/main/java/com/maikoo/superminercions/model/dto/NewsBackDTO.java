package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.NewsDO;
import lombok.Data;

@Data
public class NewsBackDTO {
    private long newsId;
    private String newsTitle;
    private String newsImg;
    private String newsContent;
    private String status;

    public static NewsBackDTO valueOf(NewsDO newsDO){
        NewsBackDTO newsBackDTO = new NewsBackDTO();
        newsBackDTO.setNewsId(newsDO.getId());
        newsBackDTO.setNewsTitle(newsDO.getTitle());
        newsBackDTO.setNewsImg(newsDO.getImgUri());
        newsBackDTO.setNewsContent(newsDO.getContent());
        newsBackDTO.setStatus(newsDO.isShowed()? "已展示" : "未展示");
        return newsBackDTO;
    }
}
