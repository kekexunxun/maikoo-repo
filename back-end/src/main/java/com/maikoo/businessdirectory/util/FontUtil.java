package com.maikoo.businessdirectory.util;

import lombok.extern.slf4j.Slf4j;
import org.springframework.core.io.ClassPathResource;
import org.springframework.stereotype.Component;

import java.awt.*;

@Slf4j
@Component
public class FontUtil {

    public Font createdFont(String fontName, int fontStyle, int fontSize){
        Font font = null;
        try {
            ClassPathResource classPathResource = new ClassPathResource("fonts/"+ fontName + ".ttf");
            font = Font.createFont(Font.TRUETYPE_FONT, classPathResource.getInputStream());
            font = font.deriveFont(fontStyle, fontSize);
        } catch (Exception e) {
            log.error("自定义失败", e);
            throw new RuntimeException(e);
        }
        return font;
    }
}
