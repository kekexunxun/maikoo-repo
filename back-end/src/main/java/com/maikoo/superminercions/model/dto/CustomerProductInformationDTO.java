package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.maikoo.superminercions.model.CustomerProductDO;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class CustomerProductInformationDTO {
    @JsonUnwrapped
    private CustomerProductDTO customerProductDTO;
    private BigDecimal minerCumulativeProduction;
    private BigDecimal minerCurrentCountingForce;

    public static CustomerProductInformationDTO valueOf(CustomerProductDO customerProductDO){
        CustomerProductInformationDTO customerProductInformationDTO = new CustomerProductInformationDTO();
        customerProductInformationDTO.setCustomerProductDTO(CustomerProductDTO.valueOf(customerProductDO));
        customerProductInformationDTO.setMinerCumulativeProduction(customerProductDO.getOutput());
        customerProductInformationDTO.setMinerCurrentCountingForce(customerProductDO.getPerformance());
        return customerProductInformationDTO;
    }
}
