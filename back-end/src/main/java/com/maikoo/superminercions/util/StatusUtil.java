package com.maikoo.superminercions.util;

public class StatusUtil {

    public static String commonOrderStatus(int status){
        String statusString = null;
        switch (status) {
            case ConstantUtil.ORDER_STATUS_PROCESSING:
                statusString = "进行中";
                break;
            case ConstantUtil.ORDER_STATUS_COMPLETED:
                statusString = "已完成";
                break;
            case ConstantUtil.ORDER_STATUS_REJECTED:
                statusString = "被驳回";
                break;
        }
        return statusString;
    }

    public static String tradingStatus(int status) {
        return commonOrderStatus(status);
    }


    public static String slideshowStatus(boolean isEnabled){
        return isEnabled ? "已展示" : "不展示";
    }
}
