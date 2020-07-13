package com.maikoo.businessdirectory.controller.front;

import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.dto.SettingDTO;
import com.maikoo.businessdirectory.service.AppletService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

@Controller
@ResponseBody
@RequestMapping("/api/setting")
public class SettingController {
    @Autowired
    private AppletService appletService;

    @RequestMapping
    public ResponseDTO<SettingDTO> information() {
        return new ResponseDTO<>(200, "success", appletService.information());
    }
}
