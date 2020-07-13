package com.maikoo.businessdirectory.controller.common;

import com.google.common.collect.Lists;
import com.google.common.io.Files;
import com.maikoo.businessdirectory.config.CustomEnvironmentConfig;
import com.maikoo.businessdirectory.exception.ImageFormatException;
import com.maikoo.businessdirectory.exception.UploadException;
import com.maikoo.businessdirectory.model.dto.ImageDTO;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import org.apache.commons.lang3.RandomStringUtils;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;
import org.springframework.web.multipart.MultipartFile;

import java.io.File;
import java.io.IOException;
import java.util.List;

@Controller
@ResponseBody
@RequestMapping("/api")
public class UploadController {
    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;

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
}
