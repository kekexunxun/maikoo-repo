package com.maikoo.superminercions.controller.front;

import com.google.common.collect.Lists;
import com.google.common.io.Files;
import com.maikoo.superminercions.config.CustomEnvironmentConfig;
import com.maikoo.superminercions.exception.ImageFormatException;
import com.maikoo.superminercions.exception.UploadException;
import com.maikoo.superminercions.model.dto.ExchangeRateDTO;
import com.maikoo.superminercions.model.dto.ImageDTO;
import com.maikoo.superminercions.model.dto.LoginDTO;
import com.maikoo.superminercions.model.dto.ResponseDTO;
import com.maikoo.superminercions.service.CustomerService;
import org.apache.commons.lang3.RandomStringUtils;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;
import org.springframework.web.multipart.MultipartFile;

import javax.validation.constraints.NotNull;
import java.io.File;
import java.io.IOException;
import java.util.List;

@Controller
@ResponseBody
@RequestMapping("/api")
public class CommonController {
    @Autowired
    private CustomerService customerService;
    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;

    @RequestMapping("/login")
    public ResponseDTO<LoginDTO> login(@NotNull String username, @NotNull String password) {
        ResponseDTO<LoginDTO> loginResponseDTO = new ResponseDTO<>(200, "登录成功", customerService.login(username, password));
        return loginResponseDTO;
    }

    @RequestMapping("/password/reset")
    public ResponseDTO resetPassword(String password, @RequestParam("mobile") String phone) {
        customerService.resetPassword(password, phone);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/image/upload")
    public ResponseDTO<ImageDTO> imageUpload(@RequestParam("file") MultipartFile file) {
        ImageDTO imageDTO = new ImageDTO();
        try {
            boolean isExistFlag = true;
            do {
                String extension = Files.getFileExtension(file.getOriginalFilename()).toUpperCase();
                List<String> extensions = Lists.newArrayList("JPEG", "JPG", "PNG", "GIF");
                if (!extensions.contains(extension)) {
                    throw new ImageFormatException();
                }
                String fileName = RandomStringUtils.random(16, true, false);
                String imageRelativePath = customEnvironmentConfig.getImageLocation() + fileName + "." + Files.getFileExtension(file.getOriginalFilename());
                File serverFile = new File(customEnvironmentConfig.getUploadLocation() + imageRelativePath);
                if (!java.nio.file.Files.exists(serverFile.toPath())) {
                    file.transferTo(serverFile);
                    imageDTO.setImgSrc("/" + imageRelativePath);
                    break;
                }
            } while (isExistFlag);
        } catch (IOException e) {
            throw new UploadException(e);
        }
        return new ResponseDTO<>(200, "上传成功", imageDTO);
    }

    @RequestMapping("/exchange/rate")
    public ResponseDTO<ExchangeRateDTO> exchangeRate(){
        return new ResponseDTO<>(200, "获取成功", customerService.exchangeRate());
    }

    @RequestMapping("/captcha/send")
    public ResponseDTO sendCaptcha(@RequestParam("mobile") String phone){
        customerService.sendCaptcha(phone);
        return new ResponseDTO(200, "发送成功", null);
    }

    @RequestMapping("/captcha/check")
    public ResponseDTO checkCaptcha(@RequestParam("mobile") String phone, @RequestParam("verifyCode") String captcha){
        customerService.checkCaptcha(phone, captcha);
        return new ResponseDTO(200, "验证码校验成功", null);
    }
}
