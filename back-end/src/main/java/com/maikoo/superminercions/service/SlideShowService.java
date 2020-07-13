package com.maikoo.superminercions.service;

import com.maikoo.superminercions.model.dto.SlideshowDTO;
import com.maikoo.superminercions.model.query.SlideShowQuery;

import java.util.List;

public interface SlideShowService {
    /**
     *
     * @return 获取轮播图列表
     */
    List<SlideshowDTO> getSlideShowList();

    /**
     *  增加轮播图
     * @param slideShowQuery 轮播图信息
     */
    void addSlideShow(SlideShowQuery slideShowQuery);

    /**
     * 删除轮播图
     * @param bannerId 轮播图id
     */
    void removeSlideShow(int bannerId);

    /**
     * 更新轮播图
     * @param slideShowQuery 轮播图信息
     */
    void updateSlideShowInfo(SlideShowQuery slideShowQuery);

    /**
     * 更新轮播图的状态
     * @param slideShowQuery
     */
    void updateSlideShowStatus(SlideShowQuery slideShowQuery);

}
