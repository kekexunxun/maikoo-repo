package com.maikoo.businessdirectory.util;

import javax.imageio.ImageIO;
import java.awt.*;
import java.awt.geom.Ellipse2D;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.util.Iterator;

public class WatermarkUtil {

    private WatermarkUtil() {

    }

    public static Builder of(String file) throws IOException {
        return Builder.ofString(file);
    }

    public static class Builder {
        private final BufferedImage source;
        private FontUtil fontUtil;

        private Builder(BufferedImage source) {
            this.source = source;
            fontUtil = new FontUtil();
        }

        private static Builder ofString(String filename) throws IOException {
            return new Builder(ImageIO.read(new File(filename)));
        }

        public Builder watermarkCircularImage(BufferedImage watermarkBufferedImage, int x, int y, boolean isCentered) {
            return watermarkCircularImage(watermarkBufferedImage, watermarkBufferedImage.getWidth(), watermarkBufferedImage.getHeight(), x, y, isCentered);
        }

        public Builder watermarkCircularImage(BufferedImage watermarkBufferedImage, int width, int height, int x, int y, boolean isCentered) {
            BufferedImage circularBufferedImage = new BufferedImage(watermarkBufferedImage.getWidth(), watermarkBufferedImage.getHeight(), BufferedImage.TYPE_4BYTE_ABGR);
            Ellipse2D.Double shape = new Ellipse2D.Double(0, 0, watermarkBufferedImage.getWidth(), watermarkBufferedImage.getHeight());
            Graphics2D graphics2D = circularBufferedImage.createGraphics();
            graphics2D.setRenderingHint(RenderingHints.KEY_ANTIALIASING, RenderingHints.VALUE_ANTIALIAS_ON);
            graphics2D.setRenderingHint(RenderingHints.KEY_ANTIALIASING, RenderingHints.VALUE_ANTIALIAS_ON);
            graphics2D.setClip(shape);
            graphics2D.drawImage(watermarkBufferedImage, 0, 0, null);
            graphics2D.dispose();

            return watermarkForImage(circularBufferedImage, width, height, x, y, isCentered);
        }

        /**
         * 图片加水印，水印类型图片
         *
         * @param watermarkBufferedImage 水印图片
         * @param width                  水印宽度
         * @param height                 水印高度
         * @param x                      x坐标，水平向右
         * @param y                      y坐标，竖直向下
         * @param isCentered             是否居中
         * @return 当前构建器
         */
        public Builder watermarkForImage(BufferedImage watermarkBufferedImage, int width, int height, int x, int y, boolean isCentered) {
            Graphics graphics = source.createGraphics();
            if (isCentered) {
                graphics.drawImage(watermarkBufferedImage, x + (source.getWidth() - width) / 2, y, width, height, null);
            } else {
                graphics.drawImage(watermarkBufferedImage, x, y, width, height, null);
            }
            graphics.dispose();
            return this;
        }

        /**
         * 图片加水印，水印类型：图片
         *
         * @param watermarkBufferedImage 水印图片
         * @param x                      x坐标，水平向右
         * @param y                      y坐标，竖直向下
         * @param isCentered             是否居中
         * @return 当前构建器
         */
        public Builder watermarkForImage(BufferedImage watermarkBufferedImage, int x, int y, boolean isCentered) {
            return watermarkForImage(watermarkBufferedImage, watermarkBufferedImage.getWidth(), watermarkBufferedImage.getHeight(), x, y, isCentered);
        }

        /**
         * 图片加水印(可选择是否居中)，水印类型：文字
         *
         * @param text                 水印内容
         * @param color                字体颜色
         * @param fontName            字体名称
         * @param fontStyle            字体样式
         * @param fontSize             字体大小
         * @param x                    x坐标，水平向右
         * @param y                    y坐标，竖直向下
         * @param isCentered           是否水平居中
         * @param isCenteredVertically 是否垂直居中
         * @return 当前构建器
         */
        public Builder watermarkForText(String text, Color color, String fontName, int fontStyle, int fontSize, int x, int y, boolean isCentered, boolean isCenteredVertically) {
            Graphics2D graphics = source.createGraphics();
            graphics.setRenderingHint(RenderingHints.KEY_TEXT_ANTIALIASING,
                    RenderingHints.VALUE_TEXT_ANTIALIAS_GASP);

            Font font = fontUtil.createdFont(fontName, fontStyle, fontSize);

            graphics.setColor(color);
            graphics.setFont(font);
            FontMetrics metrics = graphics.getFontMetrics(font);

            if (isCentered) {
                x += (source.getWidth() - metrics.stringWidth(text)) / 2;
            }

            if (isCenteredVertically) {
                y += ((source.getHeight() - metrics.getHeight()) / 2);
            }

            y += metrics.getAscent();

            graphics.drawString(text, x, y);
            graphics.dispose();
            return this;
        }

        public Builder loopWatermarkForText(Iterator<String> text, Color color, String fontName, int fontStyle, int fontSize, int x, int y, boolean isCentered, boolean isCenteredVertically) {
            Graphics2D graphics = source.createGraphics();
            Font font = fontUtil.createdFont(fontName, fontStyle, fontSize);
            FontMetrics metrics = graphics.getFontMetrics(font);
            int i = 0;
            for (; text.hasNext() && i < 3; i++) {
                StringBuilder stringBuilder = new StringBuilder(text.next());
                if (i == 2) {
                    stringBuilder.append("...");
                }
                watermarkForText(stringBuilder.toString(), color, fontName, fontStyle, fontSize, x, y + i * metrics.getHeight() + 3, isCentered, isCenteredVertically);
            }
            return this;
        }

        public BufferedImage toBufferedImage() {
            return source;
        }

        public void toFile(String filename, String formatName) throws IOException {
            ImageIO.write(source, formatName, new File(filename));
        }
    }
}
