package com.maikoo.businessdirectory.service.impl;

import com.maikoo.businessdirectory.dao.AddressDao;
import com.maikoo.businessdirectory.model.AddressDO;
import com.maikoo.businessdirectory.service.AddressService;
import lombok.extern.slf4j.Slf4j;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.util.List;

@Slf4j
@Service
public class AddressServiceImpl implements AddressService {
    @Autowired
    private AddressDao addressDao;

    @Override
    public String address(List<String> addressCodeList) {
        StringBuilder addressBuilder = new StringBuilder();
        addressCodeList.forEach(addressCode -> {
            AddressDO addressDO = addressDao.selectByAddressCode(addressCode);
            addressBuilder.append(addressDO != null ? addressDO.getAddrName() : "");
        });
        return addressBuilder.toString();
    }
}
