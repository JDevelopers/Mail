<form action="<?php echo AP_INDEX_FILE;?>?mode=submit" method="POST" id="<?php $this->data->PrintInputValue('inputMode'); ?>_form" <?php $this->data->PrintValue('hideClass_'.$this->data->GetInputValue('inputMode')); ?>>
<input type="hidden" name="form_id" value="<?php $this->data->PrintInputValue('inputMode'); ?>" />

<table class="wm_admin_center" width="550" height="">
	<tr>
		<td colspan="2"><br /></td>
	</tr>
	<tr>
		<td colspan="2">
			<span style="font-size: 14px">Step <?php $this->data->PrintValue('StepCount'); ?>:</span>
			<br />
			<span style="font-size: 18px">Read and Accept The License Agreement</span>
		</td>
	</tr>
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2">
			<div style="height:347px; overflow: auto">
		        <h1>AfterLogic Software License Agreement</h1>
		        <p class="wmh_reg">
		        AfterLogic Corporation
		        <br>
		        <a href="http://www.afterlogic.com" target="_blank">http://www.afterlogic.com</a>
		        <h4>1. IMPORTANT NOTICE.</h4>
		        YOU SHOULD READ THE FOLLOWING TERMS AND CONDITIONS CAREFULLY BEFORE YOU 
		        DOWNLOAD, INSTALL OR USE AFTERLOGIC'S PROPRIETARY SOFTWARE AND RELATED 
		        DOCUMENTATION (THE "LICENSED SOFTWARE") DISTRIBUTED UNDER THE TRADEMARK OF 
		        MAILBEE AND/OR AFTERLOGIC. BY INSTALLING OR USING THE LICENSED SOFTWARE, YOU AGREE TO BE BOUND BY 
		        THIS LICENSE AGREEMENT, AND ITS TERMS SHALL BE BINDING WITH RESPECT TO YOUR USE 
		        OF THE LICENSED SOFTWARE. IF YOU DO NOT AGREE TO THE FOLLOWING TERMS AND 
		        CONDITIONS, DO NOT INSTALL OR USE THE SOFTWARE.
		        <br>
		        <h4>2. DEFINITIONS.</h4>
		        When used in this Agreement, "AfterLogic" means AfterLogic Corporation, located 
		        in Newark, DE, USA, and the words "You" and "Your" mean the party purchasing a 
		        license to use the Licensed Software under the terms of this agreement.
		        <br>
		        <br>
		        "Licensed Software" means compiled Objects, Modules, License Key and any and 
		        all updates thereto, together with all associated documentation provided by 
		        AfterLogic or its authorized resellers. Licensed Software also means uncompiled 
		        source code if such source code is provided to You by AfterLogic.
		        <br>
		        <br>
		        "License Key" means a unique code provided by AfterLogic or its authorized 
		        resellers which identifies You, as well as the license type, and which unlocks 
		        or enables certain features of the Licensed Software.
		        <br>
		        <br>
		        "Application" or "Your Application" means a software application that You 
		        develop which incorporates all or parts of the Licensed Software.
		        <br>
		        <br>
		        "Evaluation Trial Period" means a specified period of time during which You may 
		        temporarily use the Licensed Software for evaluation purposes only.
		        <br>
		        <h4>3. LICENSE GRANT.</h4>
		        The Cumulative License granted to You by AfterLogic is a combination of the 
		        Base License Grant, described in section (3A) below, which is common to every 
		        Licensed Software title covered by this agreement, and one or more supplemental 
		        License Grant which covers the specific product obtained by You from AfterLogic 
		        or its authorized resellers. Four basic types of supplemental License Grants 
		        are described in sections (3B) through (3E): Evaluation License, Developer 
		        License, Computer License, Hosting Provider License. These four basic types are 
		        hereby further defined and/or restricted as to the number of developers, 
		        servers, locations and distribution method(s), depending on the specific 
		        product(s) being licensed by You. The precise combination of the Base License 
		        Grant and one or more supplemental License Grant(s) obtained by You is 
		        identified by AfterLogic at the time of purchase or most recent upgrade.
		        <br>
		        <blockquote>
			        <h4>3A. BASE LICENSE GRANT.</h4>
			        In consideration of Your payment of applicable license fees and/or Your 
			        acceptance of the terms of this Agreement, AfterLogic hereby grants to You 
			        certain nonexclusive and nontransferable rights limited by the terms of this 
			        Agreement. The Licensed Software is licensed (not sold) to You, for use 
			        strictly under the terms of this Agreement, and AfterLogic reserves all rights 
			        not expressly granted to You herein. If You upgrade the Licensed Software to a 
			        higher-numbered version thereof or to a comparable AfterLogic product, this 
			        license is terminated and Your rights shall be limited to the license 
			        associated with the upgraded product or version.
			        <br>
			        <h4>3B. EVALUATION LICENSE.</h4>
			        In order to facilitate an efficient evaluation process of the Licensed Software 
			        by developers, AfterLogic may, at its discretion, provide specially designed, 
			        temporary License Key(s) that are encoded with an embedded expiration date. The 
			        License granted in conjunction with such License Key(s) is considered 
			        temporary, and multiple developers may use it for the sole purpose of 
			        evaluating the Licensed Software during a specific Evaluation Trial Period. 
			        Licensed Evaluation Trial Software contains mechanisms that inhibit its ability 
			        to function at a later date. It is Your responsibility to ensure that the 
			        Applications You create do not contain Licensed Evaluation Trial Software and 
			        that their ability to function at a later date is not inhibited or diminished.
			        <br>
			        <h4>3C. DEVELOPER LICENSE.</h4>
			        The following terms and conditions contained in this section (3C) apply to You 
			        ONLY if at the time of original purchase or most recent upgrade, the License 
			        granted to You by AfterLogic was defined as "Single Developer License" or "Unlimited 
			        Developer License".
			        <br>
			        <br>
			        You are hereby granted a nonexclusive, royalty-free license to integrate the 
			        Licensed Software into Your Applications and to distribute such Licensed 
			        Software in connection with said Applications, provided that (a) said 
			        Applications do not in any way compete with the Licensed Software or expose the 
			        functionality of the Licensed Software through a programmable interface; (b) 
			        each of Your Applications developed using Licensed Software is substantially 
			        larger, more complex, and contains a significantly wider range of functions as 
			        compared to the Licensed Software; (c) each of Your Applications developed 
			        using Licensed Software is designed for end users rather than for developers 
			        who would be able to build other software that would compete with the Licensed 
			        Software, and (d) You do not permit further distribution of the Licensed 
			        Software by Your end users.
			        <br>
			        <br>
			        You may embed the License Keys in the Applications You distribute, provided 
			        that the following conditions are met: (a) each such Application must be marked 
			        with a prominent copyright notice bearing Your name as declared by You during 
			        purchase of the License; (b) the License Key may not be embedded in any such 
			        Application or distributed in any other manner that makes the License Key 
			        visible to the end user, and (c) each such Application must include the 
			        following comment in its source code within close proximity to each copy of an 
			        embedded License Key: "This application utilizes a licensed copy of AfterLogic software, 
			        copyright (c) 2002-2010, which is the property of AfterLogic Corporation, 
			        www.afterlogic.com. All rights are reserved by AfterLogic. Use of any objects 
			        outside of the context of this application is a violation of United States and 
			        international copyright laws and other applicable laws."
			        <br>
			        <br>
			        For each License Key provided to You by AfterLogic, You are granted a 
			        nonexclusive License to provide the Licensed Software and/or the License Key(s) 
			        to the number of Your employee-developers as indicated by AfterLogic and 
			        further explained below. Should the number of developers with access to the 
			        Licensed Software and/or the License Key(s) ever exceed the number indicated at 
			        the time of original purchase or most recent upgrade, You agree to inform 
			        AfterLogic of such change and to upgrade Your License accordingly by paying an 
			        upgrade fee to AfterLogic in a timely manner.
			        <br>
			        <br>
			        "Single Developer License" means that only one individual developer employed by You 
			        may be given access to the Licensed Software and/or the License Key(s) for the 
			        sole purpose of developing and maintaining Your Applications. For as long as 
			        this specific individual developer is employed or engaged by You in any 
			        capacity whatsoever, no other developer may be given access to the Licensed 
			        Software and/or the License Key(s). Should said individual developer leave Your 
			        employ and cease any professional association with You, a new individual 
			        developer may then take his or her place and be given access to the Licensed 
			        Software and/or the License Key(s).
			        <br>
			        <br>
			        "Unlimited Developer License" means an unlimited number of developers at one 
			        organization may be given access to the Licensed Software and/or the License 
			        Key(s) for the sole purpose of developing and maintaining Your Applications.
			        <br>
			        <h4>3D. COMPUTER LICENSE</h4>
			        The following terms and conditions contained in this section (3D) apply to You 
			        ONLY if at the time of original purchase or most recent upgrade, the License 
			        granted to You by AfterLogic was defined as "Single Computer License" or "Unlimited 
			        Computer License".
			        <br>
			        <br>
			        Important Note: Under the terms of the Computer License, distribution of the 
			        Licensed Software or the related License Keys, in any form whatsoever, is 
			        strictly prohibited. Furthermore, the Computer License may NOT be extended by 
			        hosting providers to their hosting clients and/or subscribers. Hosting providers 
			        must select the Hosting Provider License if any functionality of the Licensed 
			        Software is to be made available, accessible or usable by their hosting clients 
			        and/or subscribers.
			        <br>
			        <br>
			        You may embed the License Keys in other Applications installed on the same 
			        physical server(s) provided that the following conditions are met: (a) each 
			        such Application must be marked with a prominent copyright notice bearing Your 
			        name as declared by You during purchase of the License; (b) the License Key may 
			        not be embedded in any such Application or stored in any other manner that 
			        makes the License Key visible to the end user, and (c) each such Application 
			        must include the following comment in its source code within close proximity to 
			        each copy of an embedded License Key: "This application utilizes a licensed 
			        copy of AfterLogic software, copyright (c) 2002-2010, which is the property of AfterLogic 
			        Corporation, www.afterlogic.com. All rights are reserved by AfterLogic. Use of 
			        any objects outside of the context of this application is a violation of United 
			        States and international copyright laws and other applicable laws."
			        <br>
			        <br>
			        "Single Computer License" means that You are granted a license to install the Licensed 
			        Software on a single physical production server, without limitation as to the 
			        number of central processing units on the server, and on any number of 
			        development workstations and servers which can only be used for testing and 
			        development purposes.
			        <br>
			        <br>
			        "Unlimited Computer License" means that You are granted a license to install the Licensed 
			        Software on any number of physical servers maintained or owned by You, without 
			        limitation as to the number of central processing units on each server.
			        <br>
	
			        <h4>3E. HOSTING PROVIDER LICENSE.</h4>
			        The following terms and conditions contained in this section (3E) apply to You 
			        ONLY if at the time of original purchase or most recent upgrade, the License 
			        granted to You by AfterLogic was defined as "Hosting Provider License."
			        <br>
			        <br>
			        Important Note: Under the terms of the Hosting Provider License, distribution 
			        of the Licensed Software or the related License Keys, in any form whatsoever, 
			        is strictly prohibited.
			        <br>
			        <br>
			        You are hereby granted a nonexclusive license to install the Licensed Software 
			        on multiple physical servers maintained or owned by You, without limitation as 
			        to the number of central processing units on each server.
			        <br>
			        <br>
			        The License Key obtained by You as part of Your Hosting Provider License may 
			        only be entered into the registry or config file of the applicable physical 
			        server provided that the License Key may not be stored in any manner that makes 
			        the License Key visible to the end user.
			        <br>
			        <br>
			        Installation of the Licensed Software on any server, accessible to your hosting 
			        clients and/or subscribers, in any manner, which would make it physically 
			        possible for Your hosting clients, subscribers, or any other individual not 
			        directly employed by Your organization, to potentially migrate, reinstall, 
			        transfer or copy the Licensed Software to any other server whatsoever would be 
			        considered unauthorized distribution and is expressly prohibited under this license.
			        <br>
			        <br>
			        In case any of Your hosting clients and/or subscribers desires to gain the type of 
			        access to the Licensed Software which may potentially allow such client and/or 
			        subscriber to migrate, reinstall, transfer or copy the Licensed Software to another 
			        server, each such client and/or subscriber would be required to obtain a separate 
			        software license from AfterLogic Corporation.
			        <br>
		        </blockquote>
		        <h4>4. RESTRICTIONS ON USE AND TRANSFER.</h4>
		        You may not sublicense, rent, lease, assign or otherwise transfer the Licensed 
		        Software or any of Your rights thereto, either in whole or in part, to anyone 
		        else, except that You may, after obtaining written permission from AfterLogic, 
		        permanently transfer the Licensed Software in its entirety, provided You retain 
		        no copies of the Licensed Software and the transferee agrees to the terms and 
		        conditions of this Agreement. Use of the Licensed Software with a License Key 
		        obtained from a source other than AfterLogic or its authorized resellers is 
		        expressly and strictly forbidden. AfterLogic reserves the right to take any and 
		        all actions that AfterLogic, in its sole discretion, deems necessary to protect 
		        against, monitor and control the use of the Licensed Software with illegal 
		        License Keys. You agree to ensure that anyone who uses any portion of the 
		        Licensed Software provided to You complies with the terms and conditions of 
		        this Agreement.
		        <br>
		        <h4>5. INTELLECTUAL PROPERTY RIGHTS.</h4>
		        You acknowledge that the Licensed Software contains copyrighted material, trade 
		        secrets, trademarks and other proprietary material of AfterLogic ("Confidential 
		        Information"), and is protected under United States and international copyright 
		        law and other applicable laws. You may not engage in any unauthorized use or 
		        disclosure of any Confidential Information. You agree that the source code of 
		        the Licensed Software is confidential and proprietary to AfterLogic. 
		        Accordingly, You may not copy the Licensed Software, or decompile, disassemble, 
		        reverse engineer or create a derivative work based upon the Licensed Software, 
		        or authorize anyone else to do so. You must reproduce and maintain all 
		        copyright notices that are contained in the Licensed Software on any copy 
		        thereof that You make or use.
		        <br>
		        <h4>6. TERM AND TERMINATION.</h4>
		        Except as otherwise provided in this Agreement, the term of the license granted 
		        herein is perpetual and becomes effective when You install or use the Licensed 
		        Software. You may terminate this license at any time by destroying any and all 
		        copies of the Licensed Software or by returning all such copies to AfterLogic. 
		        This Agreement and the associated license for the Licensed Software will 
		        terminate automatically and without provision of notice by AfterLogic if You 
		        fail to comply with any of the terms or conditions of this Agreement or if You 
		        cease permanent use of the Licensed Software, for whatever reason. Upon 
		        termination of this Agreement for any reason, You agree that You will destroy 
		        all copies of the Licensed Software or return all such copies to AfterLogic. In 
		        addition to this sentence and the previous sentence, Sections 4, 5 and 7-13 
		        shall survive any termination of this Agreement.
		        <br>
		        <h4>7. LIMITED WARRANTY.</h4>
		        AfterLogic warrants that the Licensed Software will perform substantially in 
		        accordance with its accompanying documentation, when operated in the execution 
		        environment specified in such documentation, for the warranty period ending 
		        thirty (30) days following the date on which You first install or first use the 
		        Licensed Software. This limited warranty is void if failure of the Licensed 
		        Software to conform to such warranty is caused in whole or in part by (a) any 
		        defect in any hardware or other equipment used with the Licensed Software; (b) 
		        any failure of any hardware or any other equipment used with the Licensed 
		        Software to function in accordance with applicable manufacturer's 
		        specifications for such items; (c) any alteration, modification or enhancement 
		        of the Licensed Software by You or anyone other than AfterLogic; (d) any 
		        failure by You or anyone else to follow AfterLogic's instructions with respect 
		        to proper use of the Licensed Software; or (e) improper use, abuse, accident, 
		        neglect or negligence on the part of You or anyone other than AfterLogic. 
		        AfterLogic will not be obligated to honor the limited warranty or provide any 
		        remedy thereunder unless the Licensed Software is returned to AfterLogic along 
		        with the original dated receipt. Any replacement Licensed Software will be 
		        warranted for thirty (30) days following the date on which AfterLogic provides 
		        it to You. You understand that no Licensed Software updates or upgrades are 
		        included with this limited warranty and that AfterLogic may update or revise 
		        the Licensed Software at any time and, in so doing, incurs no obligation to 
		        furnish such updates or revisions to You.
		        <br>
		        <br>
		        EXCEPT AS OTHERWISE SET FORTH IN THIS AGREEMENT, THE LICENSED SOFTWARE IS 
		        PROVIDED TO YOU "AS IS", AND AFTERLOGIC MAKES NO EXPRESS OR IMPLIED WARRANTIES 
		        WHATSOEVER WITH RESPECT TO ITS FUNCTIONALITY, CONDITION, PERFORMANCE, 
		        OPERABILITY OR USE. WITHOUT LIMITING THE FOREGOING, AFTERLOGIC DISCLAIMS ALL 
		        IMPLIED WARRANTIES INCLUDING, WITHOUT LIMITATION, ANY IMPLIED WARRANTIES OF 
		        MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE OR FREEDOM FROM INFRINGEMENT. 
		        SOME JURISDICTIONS DO NOT ALLOW THE EXCLUSION OF IMPLIED WARRANTIES, SO THE 
		        ABOVE EXCLUSIONS MAY NOT APPLY TO YOU. THIS LIMITED WARRANTY GIVES YOU SPECIFIC 
		        LEGAL RIGHTS, AND YOU MAY ALSO HAVE OTHER RIGHTS THAT VARY FROM ONE 
		        JURISDICTION TO ANOTHER.
		        <br>
		        <h4>8. LIMITATIONS OF LIABILITY.</h4>
		        YOUR SOLE AND EXCLUSIVE REMEDY FOR ANY BREACH OF THE FOREGOING LIMITED WARRANTY 
		        SHALL BE, AT AFTERLOGIC'S OPTION, EITHER (A) REPAIR OR REPLACEMENT OF THE 
		        LICENSED SOFTWARE SO THAT IT CONFORMS TO THE FOREGOING LIMITED WARRANTY, OR (B) 
		        REFUND OF THE FEE THAT YOU PAID TO LICENSE THE LICENSED SOFTWARE. IN NO EVENT 
		        SHALL AFTERLOGIC BE LIABLE FOR ANY DAMAGES OF ANY TYPE, WHETHER DIRECT OR 
		        INDIRECT, CONSEQUENTIAL, INCIDENTAL OR SPECIAL DAMAGES, INCLUDING WITHOUT 
		        LIMITATION, LOST REVENUES, LOST PROFITS, LOSSES RESULTING FROM BUSINESS 
		        INTERRUPTION OR LOSS OF DATA, REGARDLESS OF THE FORM OF ACTION OR LEGAL THEORY 
		        UNDER WHICH SUCH LIABILITY MAY BE ASSERTED, EVEN IF AFTERLOGIC HAS BEEN ADVISED 
		        OF THE POSSIBILITY OR LIKELIHOOD OF SUCH DAMAGES. AFTERLOGIC SHALL HAVE NO 
		        LIABILITY WITH RESPECT TO ANY DATA THAT IS READ, ACCESSED, STORED OR PROCESSED 
		        WITH THE LICENSED SOFTWARE, OR FOR THE COSTS OF RECOVERING ANY SUCH DATA. IN NO 
		        EVENT SHALL AFTERLOGIC'S MAXIMUM AGGREGATE LIABILITY UNDER THIS AGREEMENT 
		        EXCEED THE TOTAL FEES PAID OR PAYABLE BY YOU TO LICENSE THE LICENSED SOFTWARE. 
		        SOME JURISDICTIONS DO NOT ALLOW THE LIMITATION OR EXCLUSION OF LIABILITY FOR 
		        INCIDENTAL OR CONSEQUENTIAL DAMAGES, SO THE ABOVE LIMITATION OR EXCLUSION MAY 
		        NOT APPLY TO YOU.
		        <br>
		        <h4>9. INDEMNIFICATION.</h4>
		        You agree to defend, indemnify, and hold AfterLogic and all of its employees, 
		        agents, representatives, directors, officers, partners, shareholders, 
		        attorneys, predecessors, successors, and assigns harmless from and against any 
		        and all claims, proceedings, damages, injuries, liabilities, losses, costs, and 
		        expenses (including reasonable attorneys' fees and litigation expenses), 
		        relating to or arising from Your use of the Licensed Software, or any breach of 
		        this Agreement, except to the extent such claim relates to or arises from a 
		        violation by AfterLogic of any third party copyright, trademark, trade secret 
		        or other intellectual property right.
		        <br>
		        <h4>10. EXPORT.</h4>
		        You agree that You will not export or transmit the Licensed Software or any 
		        Applications, directly or indirectly, to any restricted countries or in any 
		        manner that would violate United States laws and regulations as shall from time 
		        to time govern the license and delivery of technology abroad by persons subject 
		        to the jurisdiction of the United States government, including the Export 
		        Administration Act of 1979, as amended, and any applicable laws or regulations 
		        issued thereafter.
		        <br>
		        <h4>11. U.S. GOVERNMENT RESTRICTED RIGHTS.</h4>
		        If You are licensing the Licensed Software on behalf of the U.S. Government or 
		        any of its agencies ("Government"), the use, duplication, reproduction, 
		        release, modification, disclosure or transfer of the Licensed Software by the 
		        Government is subject to restricted rights in accordance with Federal 
		        Acquisition Regulation ("FAR") 12.212 for civilian agencies and Defense Federal 
		        Acquisition Regulation Supplement ("DFARS") 227.7202 for military agencies. The 
		        Licensed Software is commercial. Use of the Licensed Software by the Government 
		        is further restricted in accordance with the terms and conditions of this 
		        Agreement.
		        <br>
		        <h4>12. MISCELLANEOUS.</h4>
		        If any provision of this Agreement is held to be invalid or unenforceable under 
		        any circumstances, its application in any other circumstances and the remaining 
		        provisions of this Agreement shall not be affected. No waiver of any right 
		        under this Agreement shall be effective unless given in writing by an 
		        authorized representative of AfterLogic. No waiver by AfterLogic of any right 
		        shall be deemed to be a waiver of any other right of AfterLogic arising under 
		        this Agreement. This Agreement is solely between You and AfterLogic and shall 
		        not be construed to create any third party beneficiary rights in any other 
		        individual, partnership, corporation or other entity. This Agreement shall be 
		        governed by and interpreted in accordance with the laws of the State of New 
		        York, without regard to its provisions governing conflicts of law. Any and all 
		        disputes between You and AfterLogic pertaining to this Agreement shall be 
		        submitted to one arbitrator in binding arbitration within ten miles of New York 
		        City, New York in accordance with the Commercial Rules of the American 
		        Arbitration Association ("AAA"). The arbitrator shall be experienced in 
		        computer consulting, the development of custom software, the sale of packaged 
		        software, or related services. If You and AfterLogic do not agree on an 
		        arbitrator within sixty (60) days of the institution of the arbitration, the 
		        arbitrator shall be chose by AAA. Evidence and argument may be presented in 
		        person or by telephone, fax, postal mail, electronic mail, and other methods of 
		        communication approved by the arbitrator. The prevailing party in such 
		        proceeding shall be entitled to recover its actually incurred costs, including 
		        reasonable attorney's fees, arbitration and court costs. All hearings shall be 
		        held and a written arbitration award issued within one-hundred eighty (180) 
		        days of the date on which the arbitrator is appointed. Judgment on the award 
		        shall be final and binding and may be entered in any court of competent 
		        jurisdiction.
		        <br>
		        <h4>13. ENTIRE AGREEMENT.</h4>
		        YOU AGREE THAT THIS AGREEMENT IS THE COMPLETE AND 
		        EXCLUSIVE STATEMENT OF THE AGREEMENT BETWEEN YOU AND AFTERLOGIC, AND THAT IT 
		        SUPERSEDES ANY PROPOSALS OR PRIOR AGREEMENTS, ORAL OR WRITTEN, AND ANY OTHER 
		        COMMUNICATIONS RELATING TO THE LICENSED SOFTWARE AND THE SUBJECT MATTER HEREOF. 
		        AFTERLOGIC SHALL NOT BE BOUND BY ANY PROVISION OF ANY PURCHASE ORDER, RECEIPT, 
		        ACCEPTANCE, CONFIRMATION, CORRESPONDENCE OR OTHERWISE, OR BY ANY AGREEMENT 
		        BETWEEN YOU AND ANY OTHER PARTY, UNLESS AFTERLOGIC SPECIFICALLY AGREES TO SUCH 
		        PROVISION IN WRITING IN A FORM OF A LEGAL CONTRACT, DATED AND SIGNED BY YOU AND 
		        BY AFTERLOGIC'S OFFICER OR AUTHORIZED EMPLOYEE. NO VENDOR, DISTRIBUTOR, 
		        PROVIDER, RESELLER, OEM, SALES REPRESENTATIVE, OR OTHER PERSON IS AUTHORIZED TO 
		        MODIFY THIS AGREEMENT OR TO MAKE ANY WARRANTY, REPRESENTATION OR PROMISE 
		        REGARDING THE LICENSED SOFTWARE WHICH IS DIFFERENT FROM THOSE SET FORTH IN THIS 
		        AGREEMENT.
		        <br>
		        <br>
		    </div>
		</td>
	</tr>
	
	<tr><td colspan="2"><br /></td></tr>
	<tr>
		<td colspan="2" align="center">
			<?php $this->data->PrintValue('InfoMsg'); ?>
		</td>
	</tr>
	<tr><td colspan="2"><hr size="1"></td></tr>
	<tr>
		<td align="left">
			<input type="button" name="back_btn" id="back_btn" value="Back" class="wm_install_button" style="width: 100px" onclick="javascript:<?php $this->data->PrintValue('onClickBack'); ?>" />
		</td>
		<td align="right">
			<a name="foot"></a>
			<input type="submit" name="submit_btn" id="submit_btn" value="I Agree" class="wm_install_button" style="width: 100px" />
		</td>
	</tr>
	<tr><td colspan="2"><br /><br /></td></tr>
</table>
</form>