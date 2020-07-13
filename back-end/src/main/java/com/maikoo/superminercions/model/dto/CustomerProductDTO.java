package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.CustomerProductDO;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.TimeUtil;
import lombok.Data;

@Data
public class CustomerProductDTO {
    private String minerSn;
    private String minerName;
    private String minerDate;

    public static CustomerProductDTO valueOf(CustomerProductDO customerProductDO){
        CustomerProductDTO customerProductDTO = new CustomerProductDTO();
        customerProductDTO.setMinerSn(customerProductDO.getUserProductSN());
        customerProductDTO.setMinerName(customerProductDO.getName());
        customerProductDTO.setMinerDate(TimeUtil.timeStampToDateTime(customerProductDO.getCreatedAt()).format(ConstantUtil.BASE_DATE_TIME_PATTERN));
        return customerProductDTO;
    }
}
