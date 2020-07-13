package com.maikoo.superminercions.controller.back;

import com.maikoo.superminercions.model.dto.ResponseDTO;
import com.maikoo.superminercions.model.dto.SettingDTO;
import com.maikoo.superminercions.model.query.SettingQuery;
import com.maikoo.superminercions.service.SettingService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

@Controller
@ResponseBody
@RequestMapping("/api/admin/setting")
public class SettingController {
    @Autowired
    private SettingService settingService;

    @RequestMapping("/information")
    public ResponseDTO<SettingDTO> information(){
        return new ResponseDTO(200, "获取成功", settingService.information());
    }

    @RequestMapping("/update")
    public ResponseDTO update(@Validated @RequestBody SettingQuery settingQuery){
        settingService.update(settingQuery);
        return new ResponseDTO(200, "修改成功", null);
    }
}
