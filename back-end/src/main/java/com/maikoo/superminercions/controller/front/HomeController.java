package com.maikoo.superminercions.controller.front;

import com.maikoo.superminercions.model.dto.HomeDTO;
import com.maikoo.superminercions.model.dto.ResponseDTO;
import com.maikoo.superminercions.service.HomeService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

@Controller
@ResponseBody
@RequestMapping("/api/home")
public class HomeController {
    @Autowired
    private HomeService homeService;

    @RequestMapping
    public ResponseDTO<HomeDTO> home() {
        ResponseDTO<HomeDTO> homeResponseDTO = new ResponseDTO<>(200, "获取成功", homeService.home());
        return homeResponseDTO;
    }

}
