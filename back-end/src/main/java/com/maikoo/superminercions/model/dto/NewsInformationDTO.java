package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.maikoo.superminercions.model.NewsDO;
import lombok.Data;

@Data
public class NewsInformationDTO {
    @JsonUnwrapped
    private NewsDTO newsDTO;
    private String newsImg;

    public static NewsInformationDTO valueOf(NewsDO newsDO) {
        NewsInformationDTO newsInformationDTO = new NewsInformationDTO();
        newsInformationDTO.setNewsDTO(NewsDTO.valueOf(newsDO));
        newsInformationDTO.setNewsImg(newsDO.getImgUri());
        return newsInformationDTO;
    }
}
