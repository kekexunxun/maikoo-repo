package com.maikoo.businessdirectory.util;

import com.google.common.base.Splitter;
import com.maikoo.businessdirectory.config.CustomEnvironmentConfig;
import com.maikoo.businessdirectory.model.query.PosterQuery;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

import javax.imageio.ImageIO;
import java.awt.*;
import java.awt.image.BufferedImage;
import java.io.File;
import java.security.InvalidParameterException;

@Component
public class PosterUtil {
    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;
    private final String FONT_NAME = "PingFangSC-Regular";

    public void commonPoster(PosterQuery posterQuery) throws Exception {
        String baseLocation = customEnvironmentConfig.getUploadLocation();
        String commonPosterImageLocation = customEnvironmentConfig.getCommonPosterImageLocation();

        BufferedImage tagBufferedImage = WatermarkUtil
                .of(baseLocation + (posterQuery.getTag().length() + 1) + "@3x.png")
                .watermarkForText(posterQuery.getTag(), Color.BLACK, "PingFangSC-Thin", Font.PLAIN, 39, 0, 0, true, true)
                .toBufferedImage();

        poster(posterQuery, commonPosterImageLocation)
                .watermarkForImage(tagBufferedImage, 0, 695, true)
                .toFile(baseLocation + posterQuery.getPosterUrl(), "PNG");

    }

    public void classPoster(PosterQuery posterQuery) throws Exception {
        String baseLocation = customEnvironmentConfig.getUploadLocation();
        String commonPosterImageLocation = customEnvironmentConfig.getCommonPosterImageLocation();

        String[] tagArray = posterQuery.getTag().split("_");

        if(tagArray.length != 2){
            throw new InvalidParameterException("tag数据不正确");
        }

        BufferedImage schoolTagBufferedImage = WatermarkUtil
                .of(baseLocation + (tagArray[0].length() + 1) + "@3x.png")
                .watermarkForText(tagArray[0], Color.BLACK, "PingFangSC-Thin", Font.PLAIN, 39, 0, 0, true, true)
                .toBufferedImage();

        BufferedImage classTagBufferedImage = WatermarkUtil
                .of(baseLocation + (tagArray[0].length() + 1) + "@3x.png")
                .watermarkForText(tagArray[1], Color.BLACK, "PingFangSC-Thin", Font.PLAIN, 39, 0, 0, true, true)
                .toBufferedImage();

        poster(posterQuery, commonPosterImageLocation)
                .watermarkForImage(schoolTagBufferedImage, 562 - 30 - schoolTagBufferedImage.getWidth(), 695, false)
                .watermarkForImage(classTagBufferedImage, 562 + 30, 695, false)
                .toFile(baseLocation + posterQuery.getPosterUrl(), "PNG");
    }

    public void countryPoster(PosterQuery posterQuery) throws Exception {
        String baseLocation = customEnvironmentConfig.getUploadLocation();
        String countryPosterImageLocation = customEnvironmentConfig.getCountryPosterImageLocation();

        poster(posterQuery, countryPosterImageLocation)
                .toFile(baseLocation + posterQuery.getPosterUrl(), "PNG");
    }

    private WatermarkUtil.Builder poster(PosterQuery posterQuery, String posterImageLocation) throws Exception {
        String baseLocation = customEnvironmentConfig.getUploadLocation();

        Iterable<String> briefArray = Splitter.fixedLength(17).split(posterQuery.getBrief());
        Iterable<String> addressArray = Splitter.fixedLength(17).split(posterQuery.getAddress());

        WatermarkUtil.Builder builder = WatermarkUtil.of(baseLocation + posterImageLocation)
                .watermarkCircularImage(ImageIO.read(new File(baseLocation + posterQuery.getProfilePhoto())), 180, 180, 0, 387, true)
                .watermarkForText(posterQuery.getTitle(), Color.DARK_GRAY, FONT_NAME, Font.PLAIN, 48, 0, 601, true, false)
                .loopWatermarkForText(briefArray.iterator(), Color.DARK_GRAY, FONT_NAME, Font.PLAIN, 39, 294, 833, false, false)
                .loopWatermarkForText(addressArray.iterator(), Color.DARK_GRAY, FONT_NAME, Font.PLAIN, 39, 294, 1024, false, false)
                .watermarkForImage(posterQuery.getQrCodeBufferedImage(), 240, 240, 0, 1491, true);

        return builder;
    }

}
