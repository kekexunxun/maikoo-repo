package com.maikoo.businessdirectory.util;

import com.maikoo.businessdirectory.config.CustomEnvironmentConfig;
import org.apache.commons.lang3.RandomStringUtils;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

import javax.imageio.ImageIO;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.nio.file.Path;
import java.nio.file.Paths;

@Component
public class FileUtil {
    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;

    public String saveImage(BufferedImage bufferedImage, String formatName) throws IOException {
        String basePath = customEnvironmentConfig.getUploadLocation();
        String image = customEnvironmentConfig.getImageLocation();
        String filename = filename(basePath + image, formatName);
        ImageIO.write(bufferedImage, formatName, new File(basePath + image, filename));
        return image + filename;
    }

    public String filename(String basePath, String formatName) {
        return filename(basePath, formatName, null);
    }

    public String filename(String basePath, String formatName, String prefix) {
        StringBuilder filename = new StringBuilder();
        if (prefix != null) {
            filename.append(prefix);
        }
        boolean isExisted = true;
        do {
            filename.append(RandomStringUtils.random(16, true, false) + "." + formatName);
            Path path = Paths.get(basePath, filename.toString());
            if (!path.toFile().exists()) {
                isExisted = false;

            } else {
                filename.setLength(prefix.length());
            }
        } while (isExisted);

        return filename.toString();
    }
}
