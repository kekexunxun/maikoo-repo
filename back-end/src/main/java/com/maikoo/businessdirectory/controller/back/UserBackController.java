package com.maikoo.businessdirectory.controller.back;

import com.fasterxml.jackson.annotation.JsonView;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.dto.UserDTO;
import com.maikoo.businessdirectory.model.view.UserView;
import com.maikoo.businessdirectory.service.UserService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.List;

@Controller
@ResponseBody
@RequestMapping(value = "/admin/api/admin/user")
public class UserBackController {

    @Autowired
    private UserService userService;

    @JsonView(UserView.UserList.class)
    @RequestMapping(value = "/list")
    public ResponseDTO<List<UserDTO>> getUserList(){
        return new ResponseDTO<>(200, "获取成功", userService.getUserList());
    }
}
