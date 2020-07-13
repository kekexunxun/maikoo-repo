package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.maikoo.superminercions.model.CustomerDO;
import lombok.Data;

@Data
public class CustomerBaseInformationDTO {
    @JsonUnwrapped
    private CustomerBaseDTO customerBaseDTO;
    private String bankBranch;
    private String identAboveUrl;
    private String identBelowUrl;

    public static CustomerBaseInformationDTO valueOf(CustomerDO customerDO) {
        CustomerBaseInformationDTO customerBaseInformationDTO = new CustomerBaseInformationDTO();
        customerBaseInformationDTO.setCustomerBaseDTO(CustomerBaseDTO.valueOf(customerDO));
        customerBaseInformationDTO.setBankBranch(customerDO.getBankBranch());
        customerBaseInformationDTO.setIdentAboveUrl(customerDO.getFrontIdCardUri());
        customerBaseInformationDTO.setIdentBelowUrl(customerDO.getBackIdCardUri());
        return customerBaseInformationDTO;
    }
}
