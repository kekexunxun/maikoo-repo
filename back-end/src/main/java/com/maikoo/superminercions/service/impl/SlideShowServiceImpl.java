package com.maikoo.superminercions.service.impl;


import com.maikoo.superminercions.config.CustomEnvironmentConfig;
import com.maikoo.superminercions.dao.SlideshowDao;
import com.maikoo.superminercions.exception.InvalidParameterException;
import com.maikoo.superminercions.model.NewsDO;
import com.maikoo.superminercions.model.SlideshowDO;
import com.maikoo.superminercions.model.dto.SlideshowDTO;
import com.maikoo.superminercions.model.query.SlideShowQuery;
import com.maikoo.superminercions.service.SlideShowService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.util.ArrayList;
import java.util.List;

@Service
public class SlideShowServiceImpl implements SlideShowService {

    @Autowired
    private SlideshowDao slideshowDao;

    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;


    @Override
    public List<SlideshowDTO> getSlideShowList() {
        List<SlideshowDTO> slideshowDTOList = new ArrayList<>();
        List<SlideshowDO>  slideshowDOList = slideshowDao.selectAll();
        if (slideshowDOList != null && slideshowDOList.size() > 0) {
            slideshowDOList.forEach(slideshowDO -> slideshowDTOList.add(SlideshowDTO.valueOf(slideshowDO)));
        }
        return slideshowDTOList;
    }

    @Override
    public void addSlideShow(SlideShowQuery slideShowQuery) {
        if(slideShowQuery.isStatus() == true && slideshowDao.selectEnabledCount() >= 5){
            throw new RuntimeException("展示数量超过限定数量，最大可展示的数量是5个。");
        }
        SlideshowDO slideshowDO = new SlideshowDO();
        slideshowDO.setNewsDO(new NewsDO());
        slideshowDO.getNewsDO().setId(slideShowQuery.getNavId());
        slideshowDO.setPageType(slideShowQuery.getNavType());
        slideshowDO.setImageUri(slideShowQuery.getImg());
        slideshowDO.setEnabled(slideShowQuery.isStatus());
        slideshowDao.insert(slideshowDO);
    }

    @Override
    public void removeSlideShow(int bannerId) {
        slideshowDao.delete(bannerId);
    }

    @Override
    public void updateSlideShowInfo(SlideShowQuery slideShowQuery) {
        SlideshowDO slideshowDO = new SlideshowDO();
        slideshowDO.setId(slideShowQuery.getBannerId());
        slideshowDO.setNewsDO(new NewsDO());
        slideshowDO.getNewsDO().setId(slideShowQuery.getNavId());
        slideshowDO.setPageType(slideShowQuery.getNavType());
        slideshowDO.setImageUri(slideShowQuery.getImg());

        slideshowDao.updateSlideshow(slideshowDO);
    }

    @Override
    public void updateSlideShowStatus(SlideShowQuery slideShowQuery) {
        if(slideShowQuery.getAction().getValue() == true && slideshowDao.selectEnabledCount() >= 5){
            throw new RuntimeException("展示数量超过限定数量，最大可展示的数量是5个。");
        }

        SlideshowDO slideshowDO = new SlideshowDO();
        slideshowDO.setId(slideShowQuery.getBannerId());
        slideshowDO.setEnabled(slideShowQuery.getAction().getValue());

        slideshowDao.updateSlideshowStatus(slideshowDO);
    }
}
