package com.maikoo.superminercions.controller.back;

import com.maikoo.superminercions.model.dto.ResponseDTO;
import com.maikoo.superminercions.model.dto.SlideshowDTO;
import com.maikoo.superminercions.model.query.SlideShowQuery;
import com.maikoo.superminercions.service.SlideShowService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.List;

@Controller
@ResponseBody
@RequestMapping(value = "/api/admin/slide")
public class SlideShowController {

    @Autowired
    private SlideShowService slideShowService;

    @RequestMapping("/list")
    public ResponseDTO<List<SlideshowDTO>> getSlideShowList() {
        return new ResponseDTO(200, "查询成功", slideShowService.getSlideShowList());
    }

    @RequestMapping("/add")
    public ResponseDTO addSlideShow(@Validated SlideShowQuery slideShowQuery) {
        slideShowService.addSlideShow(slideShowQuery);
        return new ResponseDTO(200, "添加成功", null);
    }

    @RequestMapping("/remove")
    public ResponseDTO removeSlideShow(@RequestParam("bannerId") int bannerId) {
        slideShowService.removeSlideShow(bannerId);
        return new ResponseDTO(200, "删除成功", null);
    }

    @RequestMapping("/update/info")
    public ResponseDTO updateSlideShowInfo(@Validated SlideShowQuery slideShowQuery) {
        slideShowService.updateSlideShowInfo(slideShowQuery);
        return new ResponseDTO(200, "更新成功", null);
    }

    @RequestMapping("/update/status")
    public ResponseDTO updateSlideShowStatus(@Validated SlideShowQuery slideShowQuery) {
        slideShowService.updateSlideShowStatus(slideShowQuery);
        return new ResponseDTO(200, "更新状态成功", null);
    }


}
