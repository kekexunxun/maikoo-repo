package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.SlideshowDO;
import lombok.Data;

@Data
public class HomeSlideshowDTO {
    private long navId;
    private long navType;
    private String img;

    public static HomeSlideshowDTO valueOf(SlideshowDO slideshowDO){
        HomeSlideshowDTO homeSlideshowDTO = new HomeSlideshowDTO();
        homeSlideshowDTO.setNavId(slideshowDO.getNewsDO().getId());
        homeSlideshowDTO.setNavType(slideshowDO.getPageType());
        homeSlideshowDTO.setImg(slideshowDO.getImageUri());
        return homeSlideshowDTO;
    }
}
