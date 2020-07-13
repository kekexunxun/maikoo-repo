package com.maikoo.superminercions.controller.back;

import com.maikoo.superminercions.model.dto.LoginDTO;
import com.maikoo.superminercions.model.dto.ResponseDTO;
import com.maikoo.superminercions.service.AdminService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

@Controller
@ResponseBody
@RequestMapping("/api/admin")
public class AdminController {
    @Autowired
    private AdminService adminService;

    @RequestMapping("/login")
    public ResponseDTO<LoginDTO> login(String username, String password){
        return new ResponseDTO<>(200, "登录成功", adminService.login(username, password));
    }

    @RequestMapping("/password/update")
    public ResponseDTO updatePassword(@RequestParam("mobile") String phone, String password){
        adminService.updatePassword(phone, password);
        return new ResponseDTO(200, "修改成功", null);
    }
}
