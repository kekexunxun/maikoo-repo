package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.maikoo.superminercions.model.SlideshowDO;
import com.maikoo.superminercions.util.StatusUtil;
import lombok.Data;

@JsonInclude(JsonInclude.Include.NON_NULL)
@Data
public class SlideshowDTO {
    private long bannerId;
    private String img;
    private String navName;
    private int sort;
    private String status;

    public static SlideshowDTO valueOf(SlideshowDO slideshowDO) {
        SlideshowDTO slideshowDTO = new SlideshowDTO();
        slideshowDTO.setBannerId(slideshowDO.getId());
        slideshowDTO.setImg(slideshowDO.getImageUri());
        slideshowDTO.setNavName(slideshowDO.getNewsDO() == null ? "" : slideshowDO.getNewsDO().getTitle());
        slideshowDTO.setStatus(StatusUtil.slideshowStatus(slideshowDO.isEnabled()));
        slideshowDTO.setSort(slideshowDO.getRank());
        return slideshowDTO;
    }
}
