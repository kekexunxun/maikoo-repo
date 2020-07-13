package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.CustomerDO;
import lombok.Data;

@Data
public class AliInformationDTO {
    private String alipay;

    public static AliInformationDTO valueOf(CustomerDO customerDO) {
        AliInformationDTO aliInformationDTO = new AliInformationDTO();
        aliInformationDTO.setAlipay(customerDO.getAli());
        return aliInformationDTO;
    }
}
