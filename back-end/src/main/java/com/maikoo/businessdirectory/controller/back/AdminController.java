package com.maikoo.businessdirectory.controller.back;

import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.dto.TokenDTO;
import com.maikoo.businessdirectory.service.AdminService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

@Controller
@ResponseBody
@RequestMapping("/admin/api/admin")
public class AdminController {
    @Autowired
    private AdminService adminService;

    @RequestMapping("/login")
    public ResponseDTO<TokenDTO> login(@RequestParam("username") String username, @RequestParam("password") String password) {
        return new ResponseDTO<>(200, "登录成功", adminService.login(username, password));
    }

    @RequestMapping("/password/update")
    public ResponseDTO updatePassword(@RequestParam("oriPass") String oldPassword, @RequestParam("newPass") String newPassword){
        adminService.updatePassword(oldPassword, newPassword);
        return new ResponseDTO(200, "修改成功");
    }
}
