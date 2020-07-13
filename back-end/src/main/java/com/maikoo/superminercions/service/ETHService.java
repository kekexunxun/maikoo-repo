package com.maikoo.superminercions.service;

import com.maikoo.superminercions.model.dto.*;
import com.maikoo.superminercions.model.query.ETHSellQuery;
import com.maikoo.superminercions.model.query.ETHSwapSMCQuery;
import com.maikoo.superminercions.model.query.ETHWithdrawalQuery;
import com.maikoo.superminercions.model.query.OrderQuery;

import java.util.List;

public interface ETHService {
    void applyWithdrawal(ETHWithdrawalQuery ethWithdrawalQuery);

    List<ETHWithdrawalDTO> withdrawalList(int pageNumber);

    ETHWithdrawalInformationDTO withdrawalInformation(long orderSN);

    void exchangeSMC(ETHSwapSMCQuery ethSwapSMCQuery);

    List<ETHExchangeSMCDTO> exchangeSMCList(int pageNumber);

    ETHExchangeSMCInformationDTO exchangeSMCInformation(long orderSN);

    void buy(ETHSwapSMCQuery ethSwapSMCQuery);

    List<ETHBuyDTO> buyList(int pageNumber);

    ETHBuyInformationDTO buyInformation(long orderSN);

    void sell(ETHSellQuery ethSellQuery);

    List<ETHSellDTO> sellList(int pageNumber);

    ETHSellInformationDTO sellInformation(long orderSN);

    List<ETHBuyWithCustomerDTO> buyListAll();

    void updateBuyStatus(OrderQuery orderQuery);

    List<ETHSellWithCustomerDTO> sellListAll();

    void updateSellStatus(OrderQuery orderQuery);

    List<ETHWithdrawalWithCustomerDTO> withdrawalListAll();

    void updateWithdrawalStatus(OrderQuery orderQuery);

    List<ETHExchangeSMCWithCustomerDTO> exchangeSMCListAll();

    void updateExchangeSMCStatus(OrderQuery orderQuery);

    void updateBuyNote(OrderQuery orderQuery);

    void updateSellNote(OrderQuery orderQuery);

    void updateWithdrawalNote(OrderQuery orderQuery);

    void updateExchangeSMCNote(OrderQuery orderQuery);
}
