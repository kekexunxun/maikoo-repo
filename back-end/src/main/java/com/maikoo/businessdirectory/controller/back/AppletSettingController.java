package com.maikoo.businessdirectory.controller.back;

import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.dto.SettingDTO;
import com.maikoo.businessdirectory.model.query.AppletQuery;
import com.maikoo.businessdirectory.service.AppletService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

@Controller
@ResponseBody
@RequestMapping("/admin/api/setting")
public class AppletSettingController {
    @Autowired
    private AppletService appletService;

    @RequestMapping("/common")
    public ResponseDTO createSetting(AppletQuery appletQuery) {
        System.out.println(appletQuery);
        appletService.appletSetting(appletQuery);
        return new ResponseDTO<>(200, "success");
    }

    @RequestMapping
    public ResponseDTO<SettingDTO> information() {
        return new ResponseDTO<>(200, "success", appletService.information());
    }
}
