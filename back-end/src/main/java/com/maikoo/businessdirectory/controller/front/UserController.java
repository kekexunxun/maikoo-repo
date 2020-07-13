package com.maikoo.businessdirectory.controller.front;

import com.fasterxml.jackson.annotation.JsonView;
import com.maikoo.businessdirectory.model.dto.PhoneDTO;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.dto.TokenDTO;
import com.maikoo.businessdirectory.model.dto.UserDTO;
import com.maikoo.businessdirectory.model.query.PhoneDecryptQuery;
import com.maikoo.businessdirectory.model.query.UserQuery;
import com.maikoo.businessdirectory.model.view.UserView;
import com.maikoo.businessdirectory.service.UserService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

@Controller
@ResponseBody
@RequestMapping("/api/user")
public class UserController {
    @Autowired
    private UserService userService;

    @RequestMapping("/login")
    public ResponseDTO<TokenDTO> login(String code){
        return new ResponseDTO<>(200, "登录成功", userService.login(code));
    }

    @RequestMapping("/authentication")
    public ResponseDTO updateAuthentication(UserQuery userQuery){
        userService.updateAuthentication(userQuery);
        return new ResponseDTO(200, "认证成功");
    }

    @RequestMapping("/phone")
    public ResponseDTO<PhoneDTO> phoneDecrypt(PhoneDecryptQuery phoneDecryptQuery){
        return new ResponseDTO(200, "创建成功", userService.phoneDecrypt(phoneDecryptQuery));
    }

    @JsonView(UserView.Base.class)
    @RequestMapping("/information")
    public ResponseDTO<UserDTO> information(){
        ResponseDTO<UserDTO> userResponseDTO = new ResponseDTO<>(200, null);
        UserDTO userDTO = userService.information();
        if(userDTO != null){
            userResponseDTO.setMsg("获取成功");
            userResponseDTO.setData(userDTO);
        }else{
            userResponseDTO.setMsg("user auth fail");
        }
        return userResponseDTO;
    }
}
